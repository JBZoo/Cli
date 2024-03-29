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
        $outputLevel ??= OutLvl::DEBUG;
        if ($this->isDisplayProfiling()) {
            $outputLevel = OutLvl::DEFAULT;
        }

        $totalTime = \number_format(\microtime(true) - $this->getStartTime(), 3);
        $curMemory = FS::format(\memory_get_usage(false));
        $maxMemory = FS::format(\memory_get_peak_usage(true));

        $this->_(
            \implode('; ', [
                "Memory Usage/Peak: <green>{$curMemory}</green>/<green>{$maxMemory}</green>",
                "Execution Time: <green>{$totalTime} sec</green>",
            ]),
            $outputLevel,
        );

        $this->_("Exit Code is \"{$exitCode}\"", $outputLevel);
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

        $colors = ['black', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white', $defaultColor];

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
                $profile    = $this->getProfileInfo();
                $memoryDiff = FS::format($profile['memory_usage_diff']);
                $totalTime  = \number_format($profile['time_diff_ms'] / 1000, 3);
                $curMemory  = \str_pad($memoryDiff, 10, ' ', \STR_PAD_LEFT);
                $profilePrefix .= "<green>[</green>+{$totalTime}s<green>/</green>{$curMemory}<green>]</green> ";
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
}
