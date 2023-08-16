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

use JBZoo\Cli\OutputMods\AbstractOutputMode;
use JBZoo\Cli\OutputMods\Cron;
use JBZoo\Cli\OutputMods\Logstash;
use JBZoo\Cli\OutputMods\Text;
use JBZoo\Cli\ProgressBars\AbstractProgressBar;
use JBZoo\Utils\Arr;
use JBZoo\Utils\Str;
use JBZoo\Utils\Vars;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

use function JBZoo\Utils\bool;
use function JBZoo\Utils\float;
use function JBZoo\Utils\int;

abstract class CliCommand extends Command
{
    protected AbstractOutputMode $outputMode;

    abstract protected function executeAction(): int;

    public function progressBar(
        iterable|int $listOrMax,
        \Closure $callback,
        string $title = '',
        bool $throwBatchException = true,
        ?AbstractOutputMode $outputMode = null,
    ): AbstractProgressBar {
        static $nestedLevel = 0;

        $outputMode ??= $this->outputMode;

        $progressBar = $outputMode->createProgressBar()
            ->setTitle($title)
            ->setCallback($callback)
            ->setThrowBatchException($throwBatchException);

        if (\is_iterable($listOrMax)) {
            $progressBar->setList($listOrMax);
        } else {
            $progressBar->setMax($listOrMax);
        }

        $nestedLevel++;
        $progressBar->setNestedLevel($nestedLevel);

        $progressBar->execute();

        $nestedLevel--;
        $progressBar->setNestedLevel($nestedLevel);

        return $progressBar;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->addOption(
                'no-progress',
                null,
                InputOption::VALUE_NONE,
                'Disable progress bar animation for logs. ' .
                'It will be used only for <info>' . Text::getName() . '</info> output format.',
            )
            ->addOption(
                'mute-errors',
                null,
                InputOption::VALUE_NONE,
                "Mute any sort of errors. So exit code will be always \"0\" (if it's possible).\n" .
                "It has major priority then <info>--non-zero-on-error</info>. It's on your own risk!",
            )
            ->addOption(
                'stdout-only',
                null,
                InputOption::VALUE_NONE,
                "For any errors messages application will use StdOut instead of StdErr. It's on your own risk!",
            )
            ->addOption(
                'non-zero-on-error',
                null,
                InputOption::VALUE_NONE,
                'None-zero exit code on any StdErr message.',
            )
            ->addOption(
                'timestamp',
                null,
                InputOption::VALUE_NONE,
                'Show timestamp at the beginning of each message.' .
                'It will be used only for <info>' . Text::getName() . '</info> output format.',
            )
            ->addOption(
                'profile',
                null,
                InputOption::VALUE_NONE,
                'Display timing and memory usage information.',
            )
            ->addOption(
                'output-mode',
                null,
                InputOption::VALUE_REQUIRED,
                "Output format. Available options:\n" . CliHelper::renderListForHelpDescription([
                    Text::getName()     => Text::getDescription(),
                    Cron::getName()     => Cron::getDescription(),
                    Logstash::getName() => Logstash::getDescription(),
                ]),
                Text::getName(),
            )
            ->addOption(
                Cron::getName(),
                null,
                InputOption::VALUE_NONE,
                'Alias for <info>--output-mode=' . Cron::getName() . '</info>. <comment>Deprecated!</comment>',
            );

        parent::configure();
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->outputMode = $this->createOutputMode($input, $output, self::getOutputFormat($input));
        $this->getCliApplication()->setOutputMode($this->outputMode);

        $exitCode = Codes::OK;

        try {
            $this->outputMode->onExecBefore();
            $this->trigger('exec.before', [$this, $this->outputMode]);
            \ob_start();
            $exitCode    = $this->executeAction();
            $echoContent = \ob_get_clean();
            if ($echoContent !== false && $echoContent !== '') {
                $this->showLegacyOutput($echoContent);
            }
        } catch (\Exception $exception) {
            $echoContent = \ob_get_clean();
            if ($echoContent !== false && $echoContent !== '') {
                $this->showLegacyOutput($echoContent);
            }

            $this->outputMode->onExecException($exception);
            $this->trigger('exception', [$this, $this->outputMode, $exception]);

            $this->outputMode->onExecAfter($exitCode);

            if (!$this->getOptBool('mute-errors')) {
                throw $exception;
            }
        }

        $exitCode = Vars::range($exitCode, 0, 255);

        if ($this->outputMode->isOutputHasErrors() && $this->getOptBool('non-zero-on-error')) {
            $exitCode = Codes::GENERAL_ERROR;
        }

        $this->outputMode->onExecAfter($exitCode);
        $this->trigger('exec.after', [$this, $this->outputMode, &$exitCode]);

        if ($this->getOptBool('mute-errors')) {
            $exitCode = 0;
        }

        return $exitCode;
    }

    /**
     * @return null|mixed
     */
    protected function getOpt(string $optionName, bool $canBeArray = true): mixed
    {
        $value = $this->outputMode->getInput()->getOption($optionName);

        if ($canBeArray && \is_array($value)) {
            return Arr::last($value);
        }

        return $value;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    protected function getOptBool(string $optionName): bool
    {
        $value = $this->getOpt($optionName);

        return bool($value);
    }

    /**
     * @param int[] $onlyExpectedOptions
     */
    protected function getOptInt(string $optionName, array $onlyExpectedOptions = []): int
    {
        $value  = $this->getOpt($optionName) ?? 0;
        $result = int($value);

        if (\count($onlyExpectedOptions) > 0 && !\in_array($result, $onlyExpectedOptions, true)) {
            throw new Exception(
                "Passed invalid value of option \"--{$optionName}={$result}\".\n" .
                'Strict expected int-values are only: ' . CliHelper::renderExpectedValues($onlyExpectedOptions),
            );
        }

        return int($value);
    }

    /**
     * @param float[] $onlyExpectedOptions
     */
    protected function getOptFloat(string $optionName, array $onlyExpectedOptions = []): float
    {
        $value  = $this->getOpt($optionName) ?? 0.0;
        $result = float($value);

        if (\count($onlyExpectedOptions) > 0 && !\in_array($result, $onlyExpectedOptions, true)) {
            throw new Exception(
                "Passed invalid value of option \"--{$optionName}={$result}\".\n" .
                'Strict expected float-values are only: ' . CliHelper::renderExpectedValues($onlyExpectedOptions),
            );
        }

        return $result;
    }

    /**
     * @param string[] $onlyExpectedOptions
     */
    protected function getOptString(string $optionName, string $default = '', array $onlyExpectedOptions = []): string
    {
        $value  = \trim((string)$this->getOpt($optionName));
        $length = \strlen($value);
        $result = $length > 0 ? $value : $default;

        if (\count($onlyExpectedOptions) > 0 && !\in_array($result, $onlyExpectedOptions, true)) {
            throw new Exception(
                "Passed invalid value of option \"--{$optionName}={$result}\".\n" .
                'Strict expected string-values are only: ' . CliHelper::renderExpectedValues($onlyExpectedOptions),
            );
        }

        return $result;
    }

    protected function getOptArray(string $optionName): array
    {
        $list = $this->getOpt($optionName, false) ?? [];

        return (array)$list;
    }

    /**
     * @param string[] $onlyExpectedOptions
     */
    protected function getOptDatetime(
        string $optionName,
        string $defaultDatetime = '1970-01-01 00:00:00',
        array $onlyExpectedOptions = [],
    ): \DateTimeImmutable {
        $value  = $this->getOptString($optionName);
        $result = $value === '' ? $defaultDatetime : $value;

        if (\count($onlyExpectedOptions) > 0 && !\in_array($result, $onlyExpectedOptions, true)) {
            throw new Exception(
                "Passed invalid value of option {$optionName}={$result}. " .
                'Strict expected string-values are only: ' . CliHelper::renderExpectedValues($onlyExpectedOptions),
            );
        }

        return new \DateTimeImmutable($result);
    }

    /**
     * Alias to write new line in std output.
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _(
        iterable|string|int|float|bool|null $messages = '',
        string $verboseLevel = '',
        array $context = [],
    ): void {
        $this->outputMode->_($messages, $verboseLevel, $context);
    }

    protected function isInfoLevel(): bool
    {
        return $this->outputMode->isInfoLevel();
    }

    protected function isWarningLevel(): bool
    {
        return $this->outputMode->isWarningLevel();
    }

    protected function isDebugLevel(): bool
    {
        return $this->outputMode->isDebugLevel();
    }

    protected function isProfile(): bool
    {
        return $this->outputMode->isDisplayProfiling();
    }

    protected function trigger(string $eventName, array $arguments = [], ?callable $continueCallback = null): int
    {
        $application = $this->getApplication();
        if ($application === null) {
            return 0;
        }

        if ($application instanceof CliApplication) {
            $eManager = $application->getEventManager();
            if ($eManager === null) {
                return 0;
            }

            return $eManager->trigger("jbzoo.cli.{$eventName}", $arguments, $continueCallback);
        }

        return 0;
    }

    protected function ask(string $question, string $default = '', bool $isHidden = false): string
    {
        $question     = \rtrim($question, ':');
        $questionText = "<yellow-r>Question:</yellow-r> {$question}";
        if (!$isHidden) {
            $questionText .= ($default !== '' ? " (Default: <i>{$default}</i>)" : '');
        }

        $questionObj = new Question($questionText . ': ', $default);
        if ($isHidden) {
            $questionObj->setHidden(true);
            $questionObj->setHiddenFallback(false);
        }

        return (string)$this->getQuestionHelper()->ask(
            $this->outputMode->getInput(),
            $this->outputMode->getOutput(),
            $questionObj,
        );
    }

    protected function askPassword(string $question, bool $randomDefault = true): string
    {
        $default = '';
        if ($randomDefault) {
            $question .= ' (Default: <i>Random</i>)';
            $default = Str::random(10, false);
        }

        return $this->ask($question, $default, true);
    }

    protected function confirmation(string $question = 'Are you sure?', bool $default = false): bool
    {
        $question     = '<yellow-r>Question:</yellow-r> ' . \trim($question);
        $defaultValue = $default ? 'Y' : 'n';
        $questionObj  = new ConfirmationQuestion(
            "{$question} (<c>Y/n</c>; Default: <i>{$defaultValue}</i>): ",
            $default,
        );

        return (bool)$this->getQuestionHelper()->ask(
            $this->outputMode->getInput(),
            $this->outputMode->getOutput(),
            $questionObj,
        );
    }

    /**
     * @param string[] $options
     */
    protected function askOption(string $question, array $options, int|float|string|null $default = null): string
    {
        $question = '<yellow-r>Question:</yellow-r> ' . \trim($question);

        $defaultValue = '';
        if ($default !== null) {
            /** @phpstan-ignore-next-line */
            $defaultValue = $options[$default] ?? $default ?: '';
            if ($defaultValue !== '') {
                $defaultValue = " (Default: <i>{$defaultValue}</i>)";
            }
        }

        $questionObj = new ChoiceQuestion($question . $defaultValue . ': ', $options, $default);
        $questionObj->setErrorMessage('The option "%s" is undefined. See the avaialable options');

        return (string)$this->getQuestionHelper()->ask(
            $this->outputMode->getInput(),
            $this->outputMode->getOutput(),
            $questionObj,
        );
    }

    protected static function getStdIn(): ?string
    {
        static $result; // It can be read only once, so we save result as internal varaible

        if ($result === null) {
            $result = '';

            while (!\feof(\STDIN)) {
                $result .= \fread(\STDIN, 1024);
            }
        }

        return $result;
    }

    private function showLegacyOutput(string $echoContent): void
    {
        $lines = \explode("\n", $echoContent);
        $lines = \array_map(static fn ($line) => \rtrim($line), $lines);
        $lines = \array_filter($lines, static fn ($line): bool => $line !== '');

        if (\count($lines) > 1) {
            $this->_($lines, OutLvl::LEGACY);
        } else {
            $this->_($echoContent, OutLvl::LEGACY);
        }
    }

    private function getQuestionHelper(): QuestionHelper
    {
        $helper = $this->getHelper('question');
        if ($helper instanceof QuestionHelper) {
            return $helper;
        }

        throw new Exception('Symfony QuestionHelper not found');
    }

    private function createOutputMode(
        InputInterface $input,
        OutputInterface $output,
        string $outputFormat,
    ): AbstractOutputMode {
        $application = $this->getCliApplication();

        if ($outputFormat === Text::getName()) {
            return new Text($input, $output, $application);
        }

        if ($outputFormat === Cron::getName()) {
            return new Cron($input, $output, $application);
        }

        if ($outputFormat === Logstash::getName()) {
            return new Logstash($input, $output, $application);
        }

        throw new Exception("Unknown output format: {$outputFormat}");
    }

    private function getCliApplication(): CliApplication
    {
        $application = $this->getApplication();
        if ($application === null) {
            throw new Exception('Application not defined. Please, use "setApplication()" method.');
        }

        if ($application instanceof CliApplication) {
            return $application;
        }

        throw new Exception('Application must be instance of "\JBZoo\Cli\CliApplication"');
    }

    private static function getOutputFormat(InputInterface $input): string
    {
        if (bool($input->getOption('cron'))) { // TODO: Must be deprecated in the future
            return Cron::getName();
        }

        return $input->getOption('output-mode') ?? Text::getName();
    }
}
