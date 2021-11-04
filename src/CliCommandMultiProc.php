<?php

/**
 * JBZoo Toolbox - Cli
 *
 * This file is part of the JBZoo Toolbox project.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    Cli
 * @license    MIT
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @link       https://github.com/JBZoo/Cli
 */

declare(strict_types=1);

namespace JBZoo\Cli;

use BluePsyduck\SymfonyProcessManager\ProcessManager;
use JBZoo\Utils\Cli;
use JBZoo\Utils\Sys;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

/**
 * Class CliCommandMultiProc
 * @package JBZoo\Cli
 */
abstract class CliCommandMultiProc extends CliCommand
{
    private const PM_DEFAULT_MAX_PROCESSES = 20;
    private const PM_DEFAULT_INTERVAL      = 100;
    private const PM_DEFAULT_START_DELAY   = 1;
    private const PM_DEFAULT_TIMEOUT       = 3600;

    /**
     * @var array
     */
    private $procPool = [];

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this
            ->addOption(
                'pm-max',
                null,
                InputOption::VALUE_REQUIRED,
                'Process Manager. The number of processes to execute in parallel (os isolated processes)',
                self::PM_DEFAULT_MAX_PROCESSES
            )
            ->addOption(
                'pm-interval',
                null,
                InputOption::VALUE_REQUIRED,
                'Process Manager. The interval to use for polling the processes, in milliseconds',
                self::PM_DEFAULT_INTERVAL
            )
            ->addOption(
                'pm-start-delay',
                null,
                InputOption::VALUE_REQUIRED,
                'Process Manager. The time to delay the start of processes to space them out, in milliseconds',
                self::PM_DEFAULT_START_DELAY
            )
            ->addOption(
                'pm-max-timeout',
                null,
                InputOption::VALUE_REQUIRED,
                'Process Manager. The max timeout for each proccess, in seconds',
                self::PM_DEFAULT_TIMEOUT
            )
            ->addOption(
                'pm-proc-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Process Manager. Unique ID of process to execute one child proccess.',
                ''
            );

        parent::configure();
    }

    /**
     * @param string $pmThreadId
     * @return int
     */
    abstract protected function executeOneProcessAction(string $pmThreadId): int;

    /**
     * @return string[]
     */
    abstract protected function getListOfChildIds(): array;

    /**
     * @phan-suppress PhanPluginPossiblyStaticProtectedMethod
     */
    protected function beforeStartAllProcesses(): void
    {
        // noop
    }

    /**
     * @param array $procPool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @phan-suppress PhanPluginPossiblyStaticProtectedMethod
     * @phan-suppress PhanUnusedProtectedNoOverrideMethodParameter
     */
    protected function afterFinishAllProcesses(array $procPool): void
    {
        // noop
    }

    /**
     * @return int
     */
    protected function executeAction(): int
    {
        if ($pmProcId = $this->getOptString('pm-proc-id')) {
            return $this->executeOneProcessAction($pmProcId);
        }

        return $this->executeMultiProcessAction();
    }

    /**
     * @return int
     */
    protected function executeMultiProcessAction(): int
    {
        $procManager = $this->initProcManager(
            $this->getOptInt('pm-max') ?: self::PM_DEFAULT_MAX_PROCESSES,
            $this->getOptInt('pm-interval') ?: self::PM_DEFAULT_INTERVAL,
            $this->getOptInt('pm-start-delay') ?: self::PM_DEFAULT_START_DELAY
        );

        $procListIds = $this->getListOfChildIds();
        foreach ($procListIds as $procListId) {
            $childProcess = $this->createSubProcess($procListId);
            $procManager->addProcess($childProcess);
        }

        $this->beforeStartAllProcesses();
        $procManager->waitForAllProcesses();
        $this->afterFinishAllProcesses($this->procPool);

        $errorList = $this->getErrorList();
        if (count($errorList) > 0) {
            throw new Exception(implode("\n" . str_repeat('-', 60) . "\n", $errorList));
        }

        $warningList = $this->getWarningList();
        if (count($warningList) > 0) {
            $this->_(implode("\n" . str_repeat('-', 60) . "\n", $warningList), 'warn');
        }

        return 0;
    }

    /**
     * @param int $numberOfParallelProcesses
     * @param int $pollInterval
     * @param int $processStartDelay
     * @return ProcessManager
     */
    private function initProcManager(
        int $numberOfParallelProcesses,
        int $pollInterval,
        int $processStartDelay
    ): ProcessManager {
        $finishCallback = function (Process $process): void {
            $virtProcId = \spl_object_id($process);

            $exitCode = $process->getExitCode();
            $errorOutput = \trim($process->getErrorOutput());
            $stdOutput = \trim($process->getOutput());

            $this->procPool[$virtProcId]['time_end'] = \microtime(true);
            $this->procPool[$virtProcId]['exit_code'] = $exitCode;
            $this->procPool[$virtProcId]['std_out'] = $stdOutput;

            if ($exitCode > 0) {
                $this->procPool[$virtProcId]['err_out'] = $errorOutput;
            } elseif ($errorOutput) {
                $this->procPool[$virtProcId]['err_out'] = $errorOutput;
            }
        };

        return (new ProcessManager())
            ->setPollInterval($pollInterval)
            ->setNumberOfParallelProcesses($numberOfParallelProcesses)
            ->setProcessStartDelay($processStartDelay)
            ->setProcessStartCallback(function (Process $process): void {
                $virtProcId = \spl_object_id($process);
                $this->procPool[$virtProcId]['time_start'] = \microtime(true);
            })
            ->setProcessFinishCallback($finishCallback)
            ->setProcessTimeoutCallback(function (Process $process) use ($finishCallback): void {
                $finishCallback($process);

                $virtProcId = \spl_object_id($process);
                $this->procPool[$virtProcId]['reached_timeout'] = true;
            });
    }

    /**
     * @param string $procId
     * @return Process
     */
    private function createSubProcess(string $procId): Process
    {
        // Prepare option list from the parent process
        $options = array_filter($this->input->getOptions(), static function ($optionValue): bool {
            return $optionValue !== false && $optionValue !== '';
        });

        unset($options['ansi']);

        $options['pm-proc-id'] = $procId;
        $options['no-ansi'] = null;
        $options['no-interaction'] = null;

        // Prepare $argument list from the parent process
        $arguments = $this->input->getArguments();
        $argumentsList = [];

        foreach ($arguments as $argKey => $argValue) {
            if (is_array($argValue)) {
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
            Cli::build(implode(' ', [
                Sys::getBinary(),
                CliHelper::getBinPath(),
                $this->getName(),
                implode(" ", $argumentsList)
            ]), $options),
            CliHelper::getRootPath(),
            null,
            null,
            $this->getMaxTimeout()
        );

        $this->procPool[spl_object_id($process)] = [
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

    /**
     * @return array
     */
    private function getErrorList(): array
    {
        return array_reduce($this->procPool, function (array $acc, array $procInfo): array {
            if ($procInfo['reached_timeout']) {
                $acc[] = implode("\n", [
                    "Command : {$procInfo['command']}",
                    "Error   : The process with ID \"{$procInfo['proc_id']}\""
                    . " exceeded the timeout of {$this->getMaxTimeout()} seconds.",
                ]);
            } elseif ($procInfo['err_out'] && $procInfo['exit_code'] > 0) {
                $acc[] = implode("\n", [
                    "Command : {$procInfo['command']}",
                    "Code    : {$procInfo['exit_code']}",
                    "Error   : {$procInfo['err_out']}",
                    "StdOut  : {$procInfo['std_out']}",
                ]);
            }

            return $acc;
        }, []);
    }

    /**
     * @return array
     */
    private function getWarningList(): array
    {
        return array_reduce($this->procPool, static function (array $acc, array $procInfo): array {
            if ($procInfo['err_out'] && $procInfo['exit_code'] === 0) {
                $acc[] = implode("\n", [
                    "Command : {$procInfo['command']}",
                    "Warning : {$procInfo['err_out']}",
                    "StdOut  : {$procInfo['std_out']}",
                ]);
            }

            return $acc;
        }, []);
    }

    /**
     * @return int
     */
    private function getMaxTimeout(): int
    {
        return $this->getOptInt('pm-max-timeout') ?: self::PM_DEFAULT_TIMEOUT;
    }
}
