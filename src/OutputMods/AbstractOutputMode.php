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
use JBZoo\Cli\CliHelper;
use JBZoo\Cli\OutLvl;
use JBZoo\Cli\ProgressBars\AbstractProgressBar;
use Monolog\Formatter\NormalizerFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function JBZoo\Utils\bool;
use function JBZoo\Utils\isStrEmpty;

abstract class AbstractOutputMode
{
    protected CliApplication $application;

    protected float $startTimer;
    protected float $prevTime;
    protected int   $prevMemory;

    protected string $timestampFormat = 'Y-m-d\TH:i:s.uP';

    protected InputInterface  $input;
    protected OutputInterface $output;

    protected bool  $catchMode      = false;
    protected array $caughtMessages = [];

    protected array $extraContext = [];

    private bool $outputHasErrors = false;

    abstract public static function getName(): string;

    abstract public static function getDescription(): string;

    abstract public function createProgressBar(): AbstractProgressBar;

    abstract protected function printMessage(
        string $message = '',
        string $verboseLevel = OutLvl::DEFAULT,
        array $context = [],
    ): void;

    public function __construct(InputInterface $input, OutputInterface $output, CliApplication $application)
    {
        $this->prevMemory = \memory_get_usage(false);
        $this->startTimer = \microtime(true);
        $this->prevTime   = $this->startTimer;

        $this->application = $application;

        $this->input  = $input;
        $this->output = $output;

        CliHelper::setInstance($this);
    }

    public function getStartTime(): float
    {
        return $this->startTimer;
    }

    public function getInput(): InputInterface
    {
        return $this->input;
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    public function getErrOutput(): OutputInterface
    {
        if ($this->isStdoutOnly()) {
            return $this->output;
        }

        return $this->output instanceof ConsoleOutputInterface ? $this->output->getErrorOutput() : $this->output;
    }

    /**
     * Alias to write new line in std output.
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _(
        iterable|float|int|bool|string|null $messages = '',
        string $verboseLevel = OutLvl::DEFAULT,
        array $context = [],
    ): void {
        $message = $this->prepareMessages($messages, $verboseLevel);
        $context = $this->prepareContext($context);

        if ($message === null) {
            return;
        }

        if (
            $this->catchMode
            && !bool(\preg_match('/^Working on ".*"\./', $message)) // hack for system messages
        ) {
            $this->caughtMessages[] = $message;

            return;
        }

        $this->printMessage($message, $verboseLevel, $context);
    }

    public function isOutputHasErrors(): bool
    {
        return $this->outputHasErrors;
    }

    public function isStdoutOnly(): bool
    {
        return bool($this->input->getOption('stdout-only'));
    }

    public function isDisplayProfiling(): bool
    {
        return bool($this->input->getOption('profile'));
    }

    public function isDisplayTimestamp(): bool
    {
        return bool($this->input->getOption('timestamp'));
    }

    public function isInfoLevel(): bool
    {
        return $this->getOutput()->isVerbose();
    }

    public function isWarningLevel(): bool
    {
        return $this->getOutput()->isVeryVerbose();
    }

    public function isDebugLevel(): bool
    {
        return $this->getOutput()->isDebug();
    }

    public function isProgressBarDisabled(): bool
    {
        return bool($this->getInput()->getOption('no-progress'));
    }

    public function onExecBefore(): void
    {
        // empty
    }

    public function onExecException(\Exception $exception): void
    {
        $this->_($exception->getMessage(), OutLvl::ERROR);
    }

    public function onExecAfter(int $exitCode, ?string $outputLevel = null): void
    {
        $outputLevel ??= OutLvl::DEBUG;
        if ($this->isDisplayProfiling()) {
            $outputLevel = OutLvl::DEFAULT;
        }

        $this->_('Exit code: ' . $exitCode, $outputLevel);
    }

    public function catchModeStart(): void
    {
        \ob_start();
        $this->catchMode = true;
    }

    public function catchModeFinish(): array
    {
        $echoOutput = \ob_get_clean();
        if (!isStrEmpty($echoOutput)) {
            $this->caughtMessages[] = $echoOutput;
        }

        $this->catchMode = false;

        $caughtMessages = $this->caughtMessages;

        $this->caughtMessages = [];

        return $caughtMessages;
    }

    public function appendExtraContext(array $context): void
    {
        $this->extraContext = CliHelper::arrayMergeRecursiveDistinct($this->extraContext, $context);
    }

    public function getExtraContext(): array
    {
        return $this->extraContext;
    }

    public function setExtraContext(array $context): void
    {
        $this->extraContext = $context;
    }

    /**
     * @deprecated
     */
    public static function getInstance(): self
    {
        return CliHelper::getInstance();
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getProfileInfo(): array
    {
        $currentMemory = \memory_get_usage(false);
        $currentTime   = \microtime(true);

        $startTime = $_SERVER['REQUEST_TIME_FLOAT'] ?? 0.0;

        $result = [
            'memory_usage_real' => \memory_get_usage(true),
            'memory_usage'      => $currentMemory,
            'memory_usage_diff' => $currentMemory - $this->prevMemory,
            'memory_pick_real'  => \memory_get_peak_usage(true),
            'memory_pick'       => \memory_get_peak_usage(false),
            'time_total_ms'     => \round(1000 * ($currentTime - $startTime), 3),
            'time_diff_ms'      => \round(1000 * ($currentTime - $this->prevTime), 3),
        ];

        $this->prevTime   = $currentTime;
        $this->prevMemory = $currentMemory;

        return $result;
    }

    protected function prepareMessages(iterable|float|int|bool|string|null $messages, string $verboseLevel): ?string
    {
        $verboseLevel = \strtolower(\trim($verboseLevel));

        if (\is_iterable($messages)) {
            foreach ($messages as $message) {
                $this->_($message, $verboseLevel);
            }

            return null;
        }

        if ($messages === null) {
            $messages = 'null';
        } elseif (\is_bool($messages)) {
            $messages = $messages ? 'true' : 'false';
        }

        $messages = (string)$messages;

        if (\str_contains($messages, "\n")) {
            $this->_(\explode("\n", $messages), $verboseLevel);

            return null;
        }

        return $messages;
    }

    protected function prepareContext(array $context): array
    {
        $resultContext = \array_merge($this->getExtraContext(), $context);

        return (array)(new NormalizerFormatter())->normalizeValue($resultContext);
    }

    protected function markOutputHasErrors(bool $hasError = true): void
    {
        $this->outputHasErrors = $hasError;
    }
}
