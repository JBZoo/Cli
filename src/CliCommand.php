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
     * @var Helper
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $helper;

    /**
     * @var InputInterface
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected $input;

    /**
     * @var OutputInterface|ConsoleOutput
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected $output;

    /**
     * @var OutputInterface
     */
    protected $errOutput;

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this
            ->addOption('no-progress', 'p', InputOption::VALUE_NONE, "Disable progress bar rendering")
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
            ->addOption('strict', null, InputOption::VALUE_NONE, 'None-zero exit code on any StdErr message')
            ->addOption('timestamp', null, InputOption::VALUE_NONE, 'Show timestamp at the beginning of each message')
            ->addOption('profile', null, InputOption::VALUE_NONE, 'Display timing and memory usage information');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->helper = new Helper($input, $output);
        $this->input = $this->helper->getInput();
        $this->output = $this->helper->getOutput();
        $this->errOutput = $this->helper->getErrOutput();

        $exitCode = 0;
        try {
            $this->trigger('exec.before', [$this, $this->helper]);
            $exitCode = $this->executeAction();
        } catch (\Exception $exception) {
            $this->trigger('exception', [$this, $this->helper, $exception]);

            if ($this->getOptBool('mute-errors')) {
                $this->_($exception->getMessage(), Helper::VERB_EXCEPTION);
            } else {
                $this->showProfiler();
                throw $exception;
            }
        }

        if ($this->helper->isOutputHasErrors() && $this->getOptBool('strict')) {
            $exitCode = 1;
        }

        $this->trigger('exec.after', [$this, $this->helper, &$exitCode]);
        $this->showProfiler();

        if ($this->getOptBool('mute-errors')) {
            $exitCode = 0;
        }

        $this->_("Exit Code is \"{$exitCode}\"", Helper::VERB_DEBUG);

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

        if ($canBeArray && \is_array($value)) {
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
            while (!\feof(\STDIN)) {
                $result .= \fread(\STDIN, 1024);
            }
        }

        return $result;
    }

    /**
     * Alias to write new line in std output
     *
     * @param string|array $messages
     * @param string       $verboseLevel
     * @return void
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _($messages, string $verboseLevel = ''): void
    {
        $this->helper->_($messages, $verboseLevel);
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

        $this->_(\implode('; ', [
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
