<?php

/**
 * JBZoo Toolbox - Cli.
 *
 * This file is part of the JBZoo Toolbox project.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @see        https://github.com/JBZoo/Cli
 */

declare(strict_types=1);

namespace JBZoo\Cli;

use BluePsyduck\SymfonyProcessManager\ProcessManager;
use JBZoo\Cli\ProgressBars\ProgressBarProcessManager;
use JBZoo\Utils\Cli as CliUtils;
use JBZoo\Utils\Sys;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

use function JBZoo\Utils\int;

abstract class CliCommandMultiProc extends CliCommand
{
    private const PM_DEFAULT_INTERVAL    = 100;
    private const PM_DEFAULT_START_DELAY = 1;
    private const PM_DEFAULT_TIMEOUT     = 7200;

    private array                      $procPool    = [];
    private ?ProgressBarProcessManager $progressBar = null;

    abstract protected function executeOneProcess(string $pmThreadId): int;

    /**
     * @return string[]
     */
    abstract protected function getListOfChildIds(): array;

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->addOption(
                'pm-max',
                null,
                InputOption::VALUE_REQUIRED,
                'Process Manager. The number of processes to execute in parallel (os isolated processes)',
                'auto',
            )
            ->addOption(
                'pm-interval',
                null,
                InputOption::VALUE_REQUIRED,
                'Process Manager. The interval to use for polling the processes, in milliseconds',
                self::PM_DEFAULT_INTERVAL,
            )
            ->addOption(
                'pm-start-delay',
                null,
                InputOption::VALUE_REQUIRED,
                'Process Manager. The time to delay the start of processes to space them out, in milliseconds',
                self::PM_DEFAULT_START_DELAY,
            )
            ->addOption(
                'pm-max-timeout',
                null,
                InputOption::VALUE_REQUIRED,
                'Process Manager. The max timeout for each proccess, in seconds',
                self::PM_DEFAULT_TIMEOUT,
            )
            ->addOption(
                'pm-proc-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Process Manager. Unique ID of process to execute one child proccess.',
                '',
            );

        parent::configure();
    }

    /**
     * @phan-suppress PhanPluginPossiblyStaticProtectedMethod
     */
    protected function beforeStartAllProcesses(): void
    {
        // noop
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @phan-suppress PhanPluginPossiblyStaticProtectedMethod
     * @phan-suppress PhanUnusedProtectedNoOverrideMethodParameter
     */
    protected function afterFinishAllProcesses(array $procPool): void
    {
        // noop
    }

    protected function executeAction(): int
    {
        $pmProcId = $this->getOptString('pm-proc-id');
        if ($pmProcId !== '') {
            return $this->executeOneProcess($pmProcId);
        }

        return $this->executeMultiProcessAction();
    }

    protected function executeMultiProcessAction(): int
    {
        $procNum  = $this->getNumberOfProcesses();
        $cpuCores = CliHelper::getNumberOfCpuCores();
        $this->_("Max number of sub-processes: {$procNum}", OutLvl::DEBUG);
        if ($procNum > $cpuCores) {
            $this->_(
                "The specified number of processes (--pm-max={$procNum}) "
                . "is more than the found number of CPU cores in the system ({$cpuCores}).",
                OutLvl::WARNING,
            );
        }

        $procManager = $this->initProcManager($procNum, $this->gePmInterval(), $this->getPmStartDelay());

        $procListIds = $this->getListOfChildIds();

        if (!$this->outputMode->isProgressBarDisabled()) {
            $this->progressBar = new ProgressBarProcessManager($this->outputMode);
            $this->progressBar->setMax(\count($procListIds));
            $this->progressBar->start();
        }

        foreach ($procListIds as $procListId) {
            $childProcess = $this->createSubProcess($procListId);
            $procManager->addProcess($childProcess);
        }

        $this->beforeStartAllProcesses();
        $procManager->waitForAllProcesses();
        if ($this->progressBar !== null) {
            $this->progressBar->finish();
            $this->_('');
        }

        $this->afterFinishAllProcesses($this->procPool);

        $errorList = $this->getErrorList();
        if (\count($errorList) > 0) {
            throw new Exception(\implode("\n" . \str_repeat('-', 60) . "\n", $errorList));
        }

        $warningList = $this->getWarningList();
        if (\count($warningList) > 0) {
            $this->_(\implode("\n" . \str_repeat('-', 60) . "\n", $warningList), OutLvl::WARNING);
        }

        return 0;
    }

    private function initProcManager(
        int $numberOfParallelProcesses,
        int $pollInterval,
        int $processStartDelay,
    ): ProcessManager {
        $finishCallback = function (Process $process): void {
            $virtProcId = \spl_object_id($process);

            $exitCode    = $process->getExitCode();
            $errorOutput = \trim($process->getErrorOutput());
            $stdOutput   = \trim($process->getOutput());

            $this->procPool[$virtProcId]['time_end']  = \microtime(true);
            $this->procPool[$virtProcId]['exit_code'] = $exitCode;
            $this->procPool[$virtProcId]['std_out']   = $stdOutput;

            if ($exitCode > 0 || $errorOutput !== '') {
                $this->procPool[$virtProcId]['err_out'] = $errorOutput;
            }

            if ($this->progressBar !== null) {
                $this->progressBar->advance();
            }
        };

        return (new ProcessManager())
            ->setPollInterval($pollInterval)
            ->setNumberOfParallelProcesses($numberOfParallelProcesses)
            ->setProcessStartDelay($processStartDelay)
            ->setProcessStartCallback(function (Process $process): void {
                $virtProcId                                = \spl_object_id($process);
                $this->procPool[$virtProcId]['time_start'] = \microtime(true);
            })
            ->setProcessFinishCallback($finishCallback)
            ->setProcessTimeoutCallback(function (Process $process) use ($finishCallback): void {
                $finishCallback($process);

                $virtProcId                                     = \spl_object_id($process);
                $this->procPool[$virtProcId]['reached_timeout'] = true;
            });
    }

    private function createSubProcess(string $procId): Process
    {
        // Prepare option list from the parent process
        $options = \array_filter(
            $this->outputMode->getInput()->getOptions(),
            static fn ($optionValue): bool => $optionValue !== false && $optionValue !== '',
        );

        foreach (\array_keys($options) as $optionKey) {
            if (!$this->getDefinition()->getOption((string)$optionKey)->acceptValue()) {
                $options[$optionKey] = null;
            }
        }

        unset($options['ansi']);
        $options['no-ansi']        = null;
        $options['no-interaction'] = null;
        $options['pm-proc-id']     = $procId;

        // Prepare $argument list from the parent process
        $arguments     = $this->outputMode->getInput()->getArguments();
        $argumentsList = [];

        foreach ($arguments as $argKey => $argValue) {
            if (\is_array($argValue)) {
                continue;
            }

            /** @var string $argValue */
            if ($argKey !== 'command') {
                /** @phan-suppress-next-line PhanPartialTypeMismatchArgumentInternal */
                $argumentsList[] = '"' . \addcslashes($argValue, '"') . '"';
            }
        }

        // Build full command line
        $process = Process::fromShellCommandline(
            CliUtils::build(
                \implode(
                    ' ',
                    \array_filter([
                        Sys::getBinary(),
                        CliHelper::getBinPath(),
                        $this->getName(),
                        \implode(' ', $argumentsList),
                    ]),
                ),
                $options,
            ),
            CliHelper::getRootPath(),
            null,
            null,
            $this->getMaxTimeout(),
        );

        $this->procPool[\spl_object_id($process)] = [
            'command'         => $process->getCommandLine(),
            'proc_id'         => $procId,
            'exit_code'       => null,
            'std_out'         => null,
            'err_out'         => null,
            'reached_timeout' => false,
            'time_start'      => null,
            'time_end'        => null,
        ];

        return $process;
    }

    private function getErrorList(): array
    {
        return \array_reduce($this->procPool, function (array $acc, array $procInfo): array {
            if ($procInfo['reached_timeout']) {
                $acc[] = \implode("\n", [
                    "Command : {$procInfo['command']}",
                    "Error   : The process with ID \"{$procInfo['proc_id']}\""
                    . " exceeded the timeout of {$this->getMaxTimeout()} seconds.",
                ]);
            } elseif ($procInfo['err_out'] && $procInfo['exit_code'] > 0) {
                $acc[] = \implode("\n", [
                    "Command : {$procInfo['command']}",
                    "Code    : {$procInfo['exit_code']}",
                    "Error   : {$procInfo['err_out']}",
                    "StdOut  : {$procInfo['std_out']}",
                ]);
            }

            return $acc;
        }, []);
    }

    private function getWarningList(): array
    {
        return \array_reduce($this->procPool, static function (array $acc, array $procInfo): array {
            if ($procInfo['err_out'] && $procInfo['exit_code'] === 0) {
                $acc[] = \implode("\n", [
                    "Command : {$procInfo['command']}",
                    "Warning : {$procInfo['err_out']}",
                    "StdOut  : {$procInfo['std_out']}",
                ]);
            }

            return $acc;
        }, []);
    }

    private function getMaxTimeout(): int
    {
        $pmMaxTimeout = $this->getOptInt('pm-max-timeout');

        return $pmMaxTimeout > 0 ? $pmMaxTimeout : self::PM_DEFAULT_TIMEOUT;
    }

    private function gePmInterval(): int
    {
        $pmInterval = $this->getOptInt('pm-interval');

        return $pmInterval > 0 ? $pmInterval : self::PM_DEFAULT_INTERVAL;
    }

    private function getPmStartDelay(): int
    {
        $pmStartDelay = $this->getOptInt('pm-start-delay');

        return $pmStartDelay > 0 ? $pmStartDelay : self::PM_DEFAULT_START_DELAY;
    }

    private function getNumberOfProcesses(): int
    {
        $pmMax    = \strtolower($this->getOptString('pm-max'));
        $cpuCores = CliHelper::getNumberOfCpuCores();

        if ($pmMax === 'auto') {
            return $cpuCores;
        }

        $pmMaxInt = \abs(int($pmMax));

        return $pmMaxInt > 0 ? $pmMaxInt : $cpuCores;
    }
}
