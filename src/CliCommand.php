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
use JBZoo\Utils\Vars;
use Symfony\Component\Console\Command\Command;
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
     * @var Cli
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected $helper;

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this
            ->addOption('no-progress', null, InputOption::VALUE_NONE, "Disable progress bar animation for logs")
            ->addOption(
                'mute-errors',
                null,
                InputOption::VALUE_NONE,
                "Mute any sort of errors. So exit code will be always \"0\" (if it's possible).\n" .
                "It has major priority then <info>--non-zero-on-error</info>. It's on your own risk!"
            )
            ->addOption(
                'stdout-only',
                null,
                InputOption::VALUE_NONE,
                "For any errors messages application will use StdOut instead of StdErr. It's on your own risk!"
            )
            ->addOption('non-zero-on-error', null, InputOption::VALUE_NONE, 'None-zero exit code on any StdErr message')
            ->addOption('timestamp', null, InputOption::VALUE_NONE, 'Show timestamp at the beginning of each message')
            ->addOption('profile', null, InputOption::VALUE_NONE, 'Display timing and memory usage information');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->helper = new Cli($input, $output);

        $this->_('Working Directory is <i>' . \getcwd() . '</i>', Cli::DEBUG);

        $exitCode = 0;
        try {
            $this->trigger('exec.before', [$this, $this->helper]);
            \ob_start();
            $exitCode = $this->executeAction();
            if ($echoContent = \ob_get_clean()) {
                $this->showLegacyOutput($echoContent);
            }
        } catch (\Exception $exception) {
            if ($echoContent = \ob_get_clean()) {
                $this->showLegacyOutput($echoContent);
            }

            $this->trigger('exception', [$this, $this->helper, $exception]);

            if ($this->getOptBool('mute-errors')) {
                $this->_($exception->getMessage(), Cli::EXCEPTION);
            } else {
                $this->showProfiler();
                throw $exception;
            }
        }

        $exitCode = Vars::range($exitCode, 0, 255);

        if ($this->helper->isOutputHasErrors() && $this->getOptBool('non-zero-on-error')) {
            $exitCode = 1;
        }

        $this->trigger('exec.after', [$this, $this->helper, &$exitCode]);
        $this->showProfiler();

        if ($this->getOptBool('mute-errors')) {
            $exitCode = 0;
        }

        $this->_("Exit Code is \"{$exitCode}\"", Cli::DEBUG);

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
        $value = $this->helper->getInput()->getOption($optionName);

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
        $value = \trim((string)$this->getOpt($optionName));
        $length = \strlen($value);
        return $length > 0 ? $value : '';
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
     * @param string $optionName
     * @param string $defaultDatetime
     * @return \DateTimeImmutable
     */
    protected function getOptDatetime(
        string $optionName,
        string $defaultDatetime = '1970-01-01 00:00:00'
    ): \DateTimeImmutable {
        $dateAsString = $this->getOptString($optionName) ?: $defaultDatetime;
        return new \DateTimeImmutable($dateAsString);
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
    protected function _($messages = '', string $verboseLevel = ''): void
    {
        $this->helper->_($messages, $verboseLevel);
    }

    /**
     * @return bool
     */
    protected function isInfoLevel(): bool
    {
        return $this->helper->getOutput()->isVerbose();
    }

    /**
     * @return bool
     */
    protected function isWarningLevel(): bool
    {
        return $this->helper->getOutput()->isVeryVerbose();
    }

    /**
     * @return bool
     */
    protected function isDebugLevel(): bool
    {
        return $this->helper->getOutput()->isDebug();
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

        [$totalTime, $curMemory, $maxMemory] = $this->helper->getProfileInfo();

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

    /**
     * @param string|null $echoContent
     * @return void
     */
    private function showLegacyOutput(?string $echoContent): void
    {
        $echoContent = $echoContent ?: '';

        $lines = \explode("\n", $echoContent);
        $lines = \array_map(static function ($line) {
            return \rtrim($line);
        }, $lines);

        $lines = \array_filter($lines, static function ($line): bool {
            return '' !== $line;
        });

        if (\count($lines) > 1) {
            $this->_($lines, Cli::LEGACY);
        } else {
            $this->_($echoContent, Cli::LEGACY);
        }
    }
}
