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

use JBZoo\Utils\Arr;
use JBZoo\Utils\Sys;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function JBZoo\Utils\bool;
use function JBZoo\Utils\float;
use function JBZoo\Utils\int;

/**
 * Class CliCommand
 * @package JBZoo\Cli
 */
abstract class CliCommand extends Command
{
    /**
     * @var InputInterface
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected $input;

    /**
     * @var OutputInterface
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected $output;

    /**
     * @var float
     */
    private $startTime = 0.0;

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->addOption('profile', null, InputOption::VALUE_NONE, 'Display timing and memory usage information');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->startTime = microtime(true);
        $this->input = $input;
        $this->output = $output;

        $this->trigger('exec.before', [$this, $input, $output]);

        $formatter = $this->output->getFormatter();
        $colors = ['black', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white', 'default'];
        foreach ($colors as $color) {
            $formatter->setStyle($color, new OutputFormatterStyle($color));
            $formatter->setStyle("{$color}-blink", new OutputFormatterStyle($color, null, ['blink']));
            $formatter->setStyle("{$color}-bold", new OutputFormatterStyle($color, null, ['bold']));
            $formatter->setStyle("{$color}-under", new OutputFormatterStyle($color, null, ['underscore']));
            $formatter->setStyle("bg-{$color}", new OutputFormatterStyle(null, $color));
        }

        try {
            $exitCode = $this->executeAction();
        } catch (\Exception $exception) {
            $this->trigger('exception', [$this, $input, $output, $exception]);

            $this->showProfiler();
            throw $exception;
        }

        $this->trigger('exec.after', [$this, $input, $output, $exitCode]);
        $this->showProfiler();

        return $exitCode;
    }

    /**
     * @return int
     */
    abstract protected function executeAction(): int;

    /**
     * @param string $optionName
     * @param bool   $canBeArray
     * @return mixed|null
     */
    protected function getOpt(string $optionName, bool $canBeArray = true)
    {
        $value = $this->input->getOption($optionName);

        if ($canBeArray && is_array($value)) {
            return Arr::last($value);
        }

        return $value;
    }

    /**
     * @param string $optionName
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    protected function getOptBool(string $optionName): bool
    {
        $value = $this->getOpt($optionName);
        return bool($value);
    }

    /**
     * @param string $optionName
     * @return int
     */
    protected function getOptInt(string $optionName): int
    {
        $value = $this->getOpt($optionName) ?? 0;
        return int($value);
    }

    /**
     * @param string $optionName
     * @return float
     */
    protected function getOptFloat(string $optionName): float
    {
        $value = $this->getOpt($optionName) ?? 0.0;
        return float($value);
    }

    /**
     * @param string $optionName
     * @return string
     */
    protected function getOptString(string $optionName): string
    {
        $value = $this->getOpt($optionName) ?? '';
        return (string)$value;
    }

    /**
     * @param string $optionName
     * @return array
     */
    protected function getOptArray(string $optionName): array
    {
        $list = $this->getOpt($optionName, false) ?? [];
        return (array)$list;
    }

    /**
     * @return string|null
     */
    protected static function getStdIn(): ?string
    {
        // It can be read only once, so we save result as internal varaible
        static $result;

        if (null === $result) {
            $result = '';
            while (!feof(STDIN)) {
                $result .= fread(STDIN, 1024);
            }
        }

        return $result;
    }

    /**
     * Alias to write new line in std output
     *
     * @param string|array $messages
     * @param string       $verboseLevel
     * @param bool         $newline
     * @return void
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _($messages, string $verboseLevel = '', bool $newline = true): void
    {
        $verboseLevel = \strtolower(\trim($verboseLevel));

        if (is_array($messages)) {
            foreach ($messages as $message) {
                $this->_($message, $verboseLevel, $newline);
            }
            return;
        }

        $profilePrefix = '';
        if ($this->isProfile()) {
            $totalTime = number_format(microtime(true) - $this->startTime, 3);
            $curMemory = Sys::getMemory(false);
            $profilePrefix = "<green>[</green>{$curMemory}<green>/</green>{$totalTime}s<green>]</green> ";
        }

        if ($verboseLevel === '') {
            $this->output->write($profilePrefix . $messages, $newline, OutputInterface::VERBOSITY_NORMAL);
        } elseif ($verboseLevel === 'vvv') {
            $this->output->write($profilePrefix . $messages, $newline, OutputInterface::VERBOSITY_DEBUG);
        } elseif ($verboseLevel === 'vv') {
            $this->output->write($profilePrefix . $messages, $newline, OutputInterface::VERBOSITY_VERY_VERBOSE);
        } elseif ($verboseLevel === 'v') {
            $this->output->write($profilePrefix . $messages, $newline, OutputInterface::VERBOSITY_VERBOSE);
        } elseif ($verboseLevel === 'q') {
            $this->output->write($profilePrefix . $messages, $newline, OutputInterface::VERBOSITY_QUIET);
        } elseif ($verboseLevel === 'debug') {
            $this->_('<bg-magenta>Debug:</bg-magenta> ' . $messages, 'vvv', $newline);
        } elseif ($verboseLevel === 'warn') {
            $this->_('<bg-yellow>Warn:</bg-yellow> ' . $messages, 'vv', $newline);
        } elseif ($verboseLevel === 'info') {
            $this->_('<bg-blue>Info:</bg-blue> ' . $messages, 'v', $newline);
        } elseif ($verboseLevel === 'error') {
            $this->_('<bg-red>Error:</bg-red> ' . $messages, 'q', $newline);
        } else {
            throw new Exception("Undefined mode: {$verboseLevel}");
        }
    }

    /**
     * @return bool
     */
    protected function isDebug(): bool
    {
        return $this->output->getVerbosity() === OutputInterface::VERBOSITY_DEBUG;
    }

    /**
     * @return bool
     */
    protected function isProfile(): bool
    {
        return $this->getOptBool('profile');
    }

    private function showProfiler(): void
    {
        if (!$this->isProfile()) {
            return;
        }

        $totalTime = number_format(microtime(true) - $this->startTime, 3);
        $curMemory = Sys::getMemory(false);
        $maxMemory = Sys::getMemory(true);

        $this->_(implode('; ', [
            "Memory Usage: <green>{$curMemory}</green>",
            "Memory Peak: <green>{$maxMemory}</green>",
            "Total Time: <green>{$totalTime} sec</green>"
        ]));
    }

    /**
     * @param string        $eventName
     * @param array         $arguments
     * @param callable|null $continueCallback
     * @return int
     */
    protected function trigger(string $eventName, array $arguments = [], ?callable $continueCallback = null): int
    {
        $application = $this->getApplication();
        if (!$application) {
            return 0;
        }

        if ($application instanceof CliApplication) {
            $eManager = $application->getEventManager();
            if (!$eManager) {
                return 0;
            }

            return $eManager->trigger("jbzoo.cli.{$eventName}", $arguments, $continueCallback);
        }

        return 0;
    }
}
