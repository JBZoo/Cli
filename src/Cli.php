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

use JBZoo\Utils\Env;
use JBZoo\Utils\FS;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function JBZoo\Utils\bool;

/**
 * Class CliHelper
 * @package JBZoo\Cli
 */
class Cli
{
    public const TIMESTAMP_FORMAT = 'Y-m-d H:i:s.v';

    /**
     * @var $this
     */
    private static Cli $instance;

    /**
     * @var InputInterface
     */
    private InputInterface $input;

    /**
     * @var OutputInterface
     */
    private OutputInterface $output;

    /**
     * @var OutputInterface
     */
    private OutputInterface $errOutput;

    /**
     * @var float
     */
    private float $startTimer;

    /**
     * @var bool
     */
    private bool $outputHasErrors = false;

    /**
     * @var float
     */
    private float $prevTime;

    /**
     * @var int
     */
    private int $prevMemory;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->prevMemory = \memory_get_usage(false);
        $this->startTimer = \microtime(true);
        $this->prevTime = $this->startTimer;

        $this->input = $input;
        $this->output = self::addOutputStyles($output);

        $errOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $errOutput = self::addOutputStyles($errOutput);

        if ($this->isCron()) {
            $this->output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        }

        if ($this->isStdoutOnly()) {
            $this->errOutput = $this->output;
            if ($this->output instanceof ConsoleOutput) {
                $this->output->setErrorOutput($this->output);
            }
        } else {
            $this->errOutput = $errOutput;
        }

        self::$instance = $this;
    }

    /**
     * @return float
     */
    public function getStartTime(): float
    {
        return $this->startTimer;
    }

    /**
     * @return $this
     */
    public static function getInstance(): self
    {
        return self::$instance;
    }

    /**
     * @return InputInterface
     */
    public function getInput(): InputInterface
    {
        return $this->input;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * @return OutputInterface
     */
    public function getErrOutput(): OutputInterface
    {
        return $this->errOutput;
    }

    /**
     * @return string
     */
    public static function getRootPath(): string
    {
        $rootPath = \defined('JBZOO_PATH_ROOT') ? (string)\JBZOO_PATH_ROOT : null;
        if (!$rootPath) {
            return Env::string('JBZOO_PATH_ROOT');
        }

        return $rootPath;
    }

    /**
     * @return string
     */
    public static function getBinPath(): string
    {
        $binPath = \defined('JBZOO_PATH_BIN') ? (string)\JBZOO_PATH_BIN : null;
        if (!$binPath) {
            return Env::string('JBZOO_PATH_BIN');
        }

        return $binPath;
    }

    /**
     * @param OutputInterface $output
     * @return OutputInterface
     */
    public static function addOutputStyles(OutputInterface $output): OutputInterface
    {
        $formatter = $output->getFormatter();
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

        return $output;
    }

    /**
     * @return array
     */
    public function getProfileInfo(): array
    {
        $currentTime = \microtime(true);
        $currentMemory = \memory_get_usage(false);

        $currDiff = $currentMemory - $this->prevMemory;
        $result = [
            \number_format($currentTime - $this->prevTime, 3),
            ($currDiff < 0 ? '-' : '+') . FS::format(\abs($currDiff))
        ];

        $this->prevTime = $currentTime;
        $this->prevMemory = $currentMemory;

        return $result;
    }

    /**
     * Alias to write new line in std output
     *
     * @param array|string|int|float|bool|null $messages
     * @param string                           $verboseLevel
     * @return void
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function _($messages = '', string $verboseLevel = OutLvl::DEFAULT): void
    {
        $verboseLevel = \strtolower(\trim($verboseLevel));

        if (\is_array($messages)) {
            if (\count($messages) === 0) {
                return;
            }

            foreach ($messages as $message) {
                $this->_($message, $verboseLevel);
            }
            return;
        }

        if (\is_null($messages)) {
            $messages = 'null';
        } elseif (\is_bool($messages)) {
            $messages = $messages ? 'true' : 'false';
        }

        $messages = (string)$messages;

        if (\strpos($messages, "\n") !== false) {
            $this->_(\explode("\n", $messages), $verboseLevel);
            return;
        }

        $profilePrefix = '';

        if ($this->isDisplayTimestamp()) {
            $timestamp = (new \DateTimeImmutable())->format(self::TIMESTAMP_FORMAT);
            $profilePrefix .= "<green>[</green>{$timestamp}<green>]</green> ";
        }

        if ($this->isDisplayProfiling()) {
            [$totalTime, $curMemory] = $this->getProfileInfo();
            $curMemory = \str_pad($curMemory, 10, ' ', \STR_PAD_LEFT);
            $profilePrefix .= "<green>[</green>+{$totalTime}s<green>/</green>{$curMemory}<green>]</green> ";
        }

        $vNormal = OutputInterface::VERBOSITY_NORMAL;

        if ($verboseLevel === OutLvl::DEFAULT) {
            $this->output->writeln($profilePrefix . $messages, $vNormal);
        } elseif ($verboseLevel === OutLvl::V) {
            $this->output->writeln($profilePrefix . $messages, OutputInterface::VERBOSITY_VERBOSE);
        } elseif ($verboseLevel === OutLvl::VV) {
            $this->output->writeln($profilePrefix . $messages, OutputInterface::VERBOSITY_VERY_VERBOSE);
        } elseif ($verboseLevel === OutLvl::VVV) {
            $this->output->writeln($profilePrefix . $messages, OutputInterface::VERBOSITY_DEBUG);
        } elseif ($verboseLevel === OutLvl::Q) {
            $this->output->writeln($profilePrefix . $messages, OutputInterface::VERBOSITY_QUIET); // Show ALWAYS!
        } elseif ($verboseLevel === OutLvl::LEGACY) {
            $this->_('<yellow>Legacy Output:</yellow> ' . $messages);
        } elseif ($verboseLevel === OutLvl::DEBUG) {
            $this->_('<magenta>Debug:</magenta> ' . $messages, OutLvl::VVV);
        } elseif ($verboseLevel === OutLvl::WARNING) {
            $this->_('<yellow>Warning:</yellow> ' . $messages, OutLvl::VV);
        } elseif ($verboseLevel === OutLvl::INFO) {
            $this->_('<blue>Info:</blue> ' . $messages, OutLvl::V);
        } elseif ($verboseLevel === OutLvl::E) {
            $this->outputHasErrors = true;
            $this->getErrOutput()->writeln($profilePrefix . $messages, $vNormal);
        } elseif ($verboseLevel === OutLvl::ERROR) {
            $this->outputHasErrors = true;
            $this->getErrOutput()->writeln($profilePrefix . '<red-bg>Error:</red-bg> ' . $messages, $vNormal);
        } elseif ($verboseLevel === OutLvl::EXCEPTION) {
            $this->outputHasErrors = true;
            $this->getErrOutput()->writeln($profilePrefix . '<red-bg>Muted Exception:</red-bg> ' . $messages, $vNormal);
        } else {
            throw new Exception("Undefined verbose level: \"{$verboseLevel}\"");
        }
    }

    /**
     * @return bool
     */
    public function isOutputHasErrors(): bool
    {
        return $this->outputHasErrors;
    }

    /**
     * @return bool
     */
    public function isCron(): bool
    {
        return bool($this->input->getOption('cron'));
    }

    /**
     * @return bool
     */
    public function isStdoutOnly(): bool
    {
        return bool($this->input->getOption('stdout-only')) || $this->isCron();
    }

    /**
     * @return bool
     */
    public function isDisplayProfiling(): bool
    {
        return bool($this->input->getOption('profile')) || $this->isCron();
    }

    /**
     * @return bool
     */
    public function isDisplayTimestamp(): bool
    {
        return bool($this->input->getOption('timestamp')) || $this->isCron();
    }

    /**
     * @return bool
     */
    public function isInfoLevel(): bool
    {
        return $this->getOutput()->isVerbose() || $this->isCron();
    }

    /**
     * @return bool
     */
    public function isWarningLevel(): bool
    {
        return $this->getOutput()->isVeryVerbose() || $this->isCron();
    }

    /**
     * @return bool
     */
    public function isDebugLevel(): bool
    {
        return $this->getOutput()->isDebug();
    }

    /**
     * @return bool
     */
    public function isProgressBarDisabled(): bool
    {
        return bool($this->getInput()->getOption('no-progress'));
    }
}
