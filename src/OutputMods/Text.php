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

namespace JBZoo\Cli\OutputMods;

use JBZoo\Cli\CliApplication;
use JBZoo\Cli\Exception;
use JBZoo\Cli\OutLvl;
use JBZoo\Cli\ProgressBars\AbstractProgressBar;
use JBZoo\Cli\ProgressBars\ProgressBarSymfony;
use JBZoo\Utils\FS;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

use function JBZoo\Utils\bool;

class Text extends AbstractOutputMode
{
    public function __construct(InputInterface $input, OutputInterface $output, CliApplication $application)
    {
        parent::__construct($input, $output, $application);

        self::addOutputStyles($this->getOutput());
        self::addOutputStyles($this->getErrOutput());

        if ($this->output instanceof ConsoleOutput && $this->isStdoutOnly()) {
            $this->output->setErrorOutput($this->output);
        }
    }

    public function onExecBefore(): void
    {
        $this->_('Working Directory is <i>' . \getcwd() . '</i>', OutLvl::DEBUG);
    }

    public function onExecAfter(int $exitCode, ?string $outputLevel = null): void
    {
        $isParrallelExec = self::isParallelExec();

        $outputLevel ??= $isParrallelExec ? OutLvl::DEBUG : OutLvl::INFO;
        if ($this->isDisplayProfiling()) {
            $outputLevel = OutLvl::DEFAULT;
        }

        $profile = $this->getProfileInfo();

        $totalTime = \number_format(\microtime(true) - $this->getStartTime(), 3);
        $curMemory = FS::format($profile['memory_usage']);
        $maxMemory = FS::format($profile['memory_peak_real']);
        $bootTime  = (int)$profile['time_bootstrap_ms'];

        $showBootTime = $this->isDisplayProfiling() && $this->isDebugLevel();

        $this->_(
            "Memory: <green>{$curMemory}</green>; Real peak: <green>{$maxMemory}</green>; "
            . "Time: <green>{$totalTime} sec</green>"
            . ($showBootTime ? " <gray>+{$bootTime} ms (bootstrap)</gray>" : ''),
            $outputLevel,
        );

        $this->_("Exit Code is \"{$exitCode}\"", OutLvl::DEBUG);
    }

    public function onExecException(\Exception $exception): void
    {
        if (bool($this->getInput()->getOption('mute-errors'))) {
            $this->_($exception->getMessage(), OutLvl::EXCEPTION);
        }
    }

    public function createProgressBar(): AbstractProgressBar
    {
        return new ProgressBarSymfony($this);
    }

    public static function getName(): string
    {
        return 'text';
    }

    public static function getDescription(): string
    {
        return 'Default text output format, userfriendly and easy to read.';
    }

    public static function addOutputStyles(OutputInterface $output): void
    {
        $formatter    = $output->getFormatter();
        $defaultColor = 'default';

        $colors = [
            'black',
            'red',
            'green',
            'yellow',
            'blue',
            'magenta',
            'cyan',
            'white',
            'gray',
            'bright-red',
            'bright-green',
            'bright-yellow',
            'bright-blue',
            'bright-magenta',
            'bright-cyan',
            'bright-white',
            $defaultColor,
        ];

        foreach ($colors as $color) {
            $formatter->setStyle($color, new OutputFormatterStyle($color));
            $formatter->setStyle("{$color}-b", new OutputFormatterStyle($color, null, ['bold']));
            $formatter->setStyle("{$color}-u", new OutputFormatterStyle($color, null, ['underscore']));
            $formatter->setStyle("{$color}-r", new OutputFormatterStyle($color, null, ['reverse']));
            $formatter->setStyle("{$color}-bg", new OutputFormatterStyle(null, $color));
            $formatter->setStyle("{$color}-bl", new OutputFormatterStyle($color, null, ['blink']));
        }

        $formatter->setStyle('bl', new OutputFormatterStyle($defaultColor, null, ['blink']));
        $formatter->setStyle('b', new OutputFormatterStyle($defaultColor, null, ['bold']));
        $formatter->setStyle('u', new OutputFormatterStyle($defaultColor, null, ['underscore']));
        $formatter->setStyle('r', new OutputFormatterStyle(null, null, ['reverse']));
        $formatter->setStyle('bg', new OutputFormatterStyle('black', 'white'));

        // Aliases
        $formatter->setStyle('i', new OutputFormatterStyle('green')); // Alias for <info>
        $formatter->setStyle('c', new OutputFormatterStyle('yellow')); // Alias for <comment>
        $formatter->setStyle('q', new OutputFormatterStyle('black', 'cyan')); // Alias for <question>
        $formatter->setStyle('e', new OutputFormatterStyle('white', 'red')); // Alias for <error>

        $output->setFormatter($formatter);
    }

    /**
     * Alias to write new line in std output.
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function printMessage(
        string $message = '',
        string $verboseLevel = OutLvl::DEFAULT,
        array $context = [],
    ): void {
        if (\count($context) > 0) {
            $message .= ' ' . \json_encode($context, \JSON_THROW_ON_ERROR);
        }

        $profilePrefix = '';

        if ($this->isDisplayTimestamp()) {
            $timestamp = (new \DateTimeImmutable())->format($this->timestampFormat);
            $profilePrefix .= "<green>[</green>{$timestamp}<green>]</green> ";
        }

        $executePrint  = false;
        $printCallback = null;
        $vNormal       = OutputInterface::VERBOSITY_NORMAL;

        if ($verboseLevel === OutLvl::DEFAULT) {
            $executePrint  = $this->showMessage($vNormal);
            $printCallback = function (string $profilePrefix) use ($message, $vNormal): void {
                $this->getOutput()->writeln($profilePrefix . $message, $vNormal);
            };
        } elseif ($verboseLevel === OutLvl::V) {
            $executePrint  = $this->showMessage(OutputInterface::VERBOSITY_VERBOSE);
            $printCallback = function (string $profilePrefix) use ($message): void {
                $this->getOutput()->writeln($profilePrefix . $message, OutputInterface::VERBOSITY_VERBOSE);
            };
        } elseif ($verboseLevel === OutLvl::VV) {
            $executePrint  = $this->showMessage(OutputInterface::VERBOSITY_VERY_VERBOSE);
            $printCallback = function (string $profilePrefix) use ($message): void {
                $this->getOutput()->writeln($profilePrefix . $message, OutputInterface::VERBOSITY_VERY_VERBOSE);
            };
        } elseif ($verboseLevel === OutLvl::VVV) {
            $executePrint  = $this->showMessage(OutputInterface::VERBOSITY_DEBUG);
            $printCallback = function (string $profilePrefix) use ($message): void {
                $this->getOutput()->writeln($profilePrefix . $message, OutputInterface::VERBOSITY_DEBUG);
            };
        } elseif ($verboseLevel === OutLvl::Q) {
            $executePrint  = $this->showMessage(OutputInterface::VERBOSITY_QUIET);
            $printCallback = function (string $profilePrefix) use ($message): void {
                $this->getOutput()->writeln(
                    $profilePrefix . $message,
                    OutputInterface::VERBOSITY_QUIET,
                ); // Show ALWAYS!
            };
        } elseif ($verboseLevel === OutLvl::LEGACY) {
            $this->_("<yellow>Legacy Output:</yellow> {$message}");
        } elseif ($verboseLevel === OutLvl::DEBUG) {
            $this->_("<magenta>Debug:</magenta> {$message}", OutLvl::VVV);
        } elseif ($verboseLevel === OutLvl::WARNING) {
            $this->_("<yellow>Warning:</yellow> {$message}", OutLvl::VV);
        } elseif ($verboseLevel === OutLvl::INFO) {
            $this->_("<blue>Info:</blue> {$message}", OutLvl::V);
        } elseif ($verboseLevel === OutLvl::E) {
            $executePrint  = $this->showMessage($vNormal);
            $printCallback = function (string $profilePrefix) use ($message, $vNormal): void {
                $this->markOutputHasErrors(true);
                $this->getErrOutput()->writeln($profilePrefix . $message, $vNormal);
            };
        } elseif ($verboseLevel === OutLvl::ERROR) {
            $executePrint  = $this->showMessage($vNormal);
            $printCallback = function (string $profilePrefix) use ($message, $vNormal): void {
                $this->markOutputHasErrors(true);
                $this->getErrOutput()->writeln("{$profilePrefix}<red-bg>Error:</red-bg> {$message}", $vNormal);
            };
        } elseif ($verboseLevel === OutLvl::EXCEPTION) {
            $executePrint  = $this->showMessage($vNormal);
            $printCallback = function (string $profilePrefix) use ($message, $vNormal): void {
                $this->markOutputHasErrors(true);
                $this->getErrOutput()->writeln(
                    "{$profilePrefix}<red-bg>Muted Exception:</red-bg> {$message}",
                    $vNormal,
                );
            };
        } else {
            throw new Exception("Undefined verbose level: \"{$verboseLevel}\"");
        }

        if ($executePrint && $printCallback !== null) {
            if ($this->isDisplayProfiling()) {
                $profile = $this->getProfileInfo();
                $oneKb   = 1024;

                $timeTotal = \str_pad(\number_format($profile['time_total_ms'] / 1000, 2), 5, ' ', \STR_PAD_LEFT);

                $timeDiff = \str_pad(\number_format($profile['time_diff_ms'] / 1000, 2), 5, ' ', \STR_PAD_LEFT);
                $timeDiff = $timeDiff === ' 0.00' ? '<gray>    0</gray>' : $timeDiff;

                $memoryDiff = FS::format($profile['memory_usage_diff'], 0);
                $memoryDiff = \str_pad($memoryDiff, 6, ' ', \STR_PAD_LEFT);
                if (\abs($profile['memory_usage_diff']) < $oneKb) {
                    $memoryDiff = '<gray>' . \str_pad($memoryDiff, 6, ' ', \STR_PAD_LEFT) . '</gray>';
                } else {
                    $memoryDiff = $profile['memory_usage_diff'] > 0
                        ? "<yellow>{$memoryDiff}</yellow>"
                        : "<green>{$memoryDiff}</green>";
                }

                $profilerData = [];
                if ($this instanceof Cron) {
                    $profilerData[] = $timeDiff;
                    $profilerData[] = $memoryDiff;
                } else {
                    if ($this->showMessage(OutputInterface::VERBOSITY_DEBUG)) {
                        $profilerData[] = $timeTotal;
                        $profilerData[] = $timeDiff;
                        $profilerData[] = $memoryDiff;
                        $profilerData[] = \str_pad(FS::format($profile['memory_usage'], 0), 7, ' ', \STR_PAD_LEFT);
                        $profilerData[] = 'P:' . FS::format($profile['memory_peak'], 0);
                    } elseif ($this->showMessage(OutputInterface::VERBOSITY_VERY_VERBOSE)) {
                        $profilerData[] = $timeTotal;
                        $profilerData[] = $timeDiff;
                        $profilerData[] = $memoryDiff;
                        $profilerData[] = \str_pad(FS::format($profile['memory_usage'], 0), 7, ' ', \STR_PAD_LEFT);
                    } elseif ($this->showMessage(OutputInterface::VERBOSITY_VERBOSE)) {
                        $profilerData[] = $timeDiff;
                        $profilerData[] = $memoryDiff;
                        $profilerData[] = \str_pad(FS::format($profile['memory_usage'], 0), 7, ' ', \STR_PAD_LEFT);
                    } else {
                        $profilerData[] = $timeDiff;
                        $profilerData[] = $memoryDiff;
                    }
                }

                $profilePrefix .= '<green>[</green>'
                    . \implode('<green>|</green>', $profilerData)
                    . '<green>]</green> ';
            }
            $printCallback($profilePrefix);
        }
    }

    private function showMessage(int $selectedVerbosity): bool
    {
        $verbosities = OutputInterface::VERBOSITY_QUIET
            | OutputInterface::VERBOSITY_NORMAL
            | OutputInterface::VERBOSITY_VERBOSE
            | OutputInterface::VERBOSITY_VERY_VERBOSE
            | OutputInterface::VERBOSITY_DEBUG;

        $verbosity = ($verbosities & $selectedVerbosity) > 0
            ? $verbosities & $selectedVerbosity
            : OutputInterface::VERBOSITY_NORMAL;

        $curVerbose = $this->getOutput()->getVerbosity();

        return $verbosity <= $curVerbose;
    }

    /**
     * Weird hack... Need to be fixed in the future.
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private static function isParallelExec(): bool
    {
        $argv = $_SERVER['argv'] ?? [];

        foreach ($argv as $arg) {
            if (\str_contains($arg, 'pm-max')) {
                return true;
            }
        }

        return false;
    }
}
