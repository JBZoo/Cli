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

use DateTimeInterface;
use JBZoo\Utils\Arr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
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
     * @var CliHelper
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $helper;

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
     * @var OutputInterface
     */
    private $errOutput;

    /**
     * @var bool
     */
    private $outputHasErrors = false;


    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this
            ->addOption(
                'mute-errors',
                null,
                InputOption::VALUE_NONE,
                "Mute any sort of errors. So exit code will be always \"0\" (if it's possible).\n" .
                "It has major priority then <info>--strict</info>. It's on your own risk!"
            )
            ->addOption(
                'stdout-only',
                null,
                InputOption::VALUE_NONE,
                "For any errors messages application will use StdOut instead of ErrOut. It's on your own risk!"
            )
            ->addOption('strict', null, InputOption::VALUE_NONE, 'None-zero exit code on any StdErr messages')
            ->addOption('timestamp', null, InputOption::VALUE_NONE, 'Show timestamp at the beginning of each message')
            ->addOption('profile', null, InputOption::VALUE_NONE, 'Display timing and memory usage information');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->helper = new CliHelper($input, $output);
        $this->input = $this->helper->getInput();
        $this->output = $this->helper->getOutput();

        if ($this->getOptBool('stdout-only')) {
            $this->errOutput = $this->helper->getOutput();
            if ($this->output instanceof ConsoleOutput) {
                $this->output->setErrorOutput($this->output);
            }
        } else {
            $this->errOutput = $this->helper->getErrOutput();
        }

        $exitCode = 0;
        try {
            $this->trigger('exec.before', [$this, $this->helper]);
            $exitCode = $this->executeAction();
        } catch (\Exception $exception) {
            $this->trigger('exception', [$this, $this->helper, $exception]);

            if ($this->getOptBool('mute-errors')) {
                $this->_($exception->getMessage(), 'exception');
            } else {
                $this->showProfiler();
                throw $exception;
            }
        }

        if ($this->outputHasErrors && $this->getOptBool('strict')) {
            $exitCode = 1;
        }

        $this->trigger('exec.after', [$this, $this->helper, &$exitCode]);
        $this->showProfiler();

        if ($this->getOptBool('mute-errors')) {
            $exitCode = 0;
        }

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

        if ($this->getOptBool('timestamp')) {
            $timestamp = (new \DateTimeImmutable())->format(DateTimeInterface::RFC3339);
            $profilePrefix .= "<green>[</green>{$timestamp}<green>]</green> ";
        }

        if ($this->isProfile()) {
            [$totalTime, $curMemory] = $this->helper->getProfileDate();
            $profilePrefix .= "<green>[</green>{$curMemory}<green>/</green>{$totalTime}s<green>]</green> ";
        }

        if ($verboseLevel === '') {
            $this->output->write($profilePrefix . $messages, $newline, OutputInterface::VERBOSITY_NORMAL);
        } elseif ($verboseLevel === 'v') {
            $this->output->write($profilePrefix . $messages, $newline, OutputInterface::VERBOSITY_VERBOSE);
        } elseif ($verboseLevel === 'vv') {
            $this->output->write($profilePrefix . $messages, $newline, OutputInterface::VERBOSITY_VERY_VERBOSE);
        } elseif ($verboseLevel === 'vvv') {
            $this->output->write($profilePrefix . $messages, $newline, OutputInterface::VERBOSITY_DEBUG);
        } elseif ($verboseLevel === 'q') { // Show ALWAYS!
            $this->output->write($profilePrefix . $messages, $newline, OutputInterface::VERBOSITY_QUIET);
        } elseif ($verboseLevel === 'debug') {
            $this->_('<magenta>Debug:</magenta> ' . $messages, 'vvv', $newline);
        } elseif ($verboseLevel === 'warning') {
            $this->_('<yellow>Warning:</yellow> ' . $messages, 'vv', $newline);
        } elseif ($verboseLevel === 'info') {
            $this->_('<blue>Info:</blue> ' . $messages, 'v', $newline);
        } elseif ($verboseLevel === 'error') {
            $this->outputHasErrors = true;
            $this->errOutput->write(
                $profilePrefix . '<bg-red>Error:</bg-red> ' . $messages,
                $newline,
                OutputInterface::VERBOSITY_NORMAL
            );
        } elseif ($verboseLevel === 'exception') {
            $this->outputHasErrors = true;
            $this->errOutput->write(
                $profilePrefix . '<bg-red>Exception:</bg-red> ' . $messages,
                $newline,
                OutputInterface::VERBOSITY_NORMAL
            );
        } else {
            throw new Exception("Undefined mode: \"{$verboseLevel}\"");
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

        [$totalTime, $curMemory, $maxMemory] = $this->helper->getProfileDate();

        $this->_(implode('; ', [
            "Memory Usage/Peak: <green>{$curMemory}</green>/<green>{$maxMemory}</green>",
            "Execution Time: <green>{$totalTime} sec</green>"
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
