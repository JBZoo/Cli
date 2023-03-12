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

namespace JBZoo\Cli\ProgressBars;

use JBZoo\Cli\Cli;
use JBZoo\Cli\CliRender;
use JBZoo\Cli\Icons;
use JBZoo\Utils\Str;
use Symfony\Component\Console\Helper\ProgressBar as SymfonyProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ProgressBar extends AbstractProgressBar
{
    private OutputInterface     $output;
    private string              $title       = '';
    private ?SymfonyProgressBar $progressBar = null;
    private Cli                 $helper;
    private int                 $max                 = 0;
    private ?\Closure           $callback            = null;
    private bool                $throwBatchException = true;
    private string              $finishIcon;
    private string              $progressIcon;

    /** @var array|iterable */
    private iterable $list = [];

    public function __construct(?OutputInterface $output = null)
    {
        $this->helper = Cli::getInstance();
        $this->output = $output ?: $this->helper->getOutput();

        $this->progressIcon = Icons::getRandomIcon(Icons::GROUP_PROGRESS, $this->output->isDecorated());
        $this->finishIcon   = Icons::getRandomIcon(Icons::GROUP_FINISH, $this->output->isDecorated());
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setList(iterable $list): self
    {
        $this->list = $list;

        if ($list instanceof \Countable) {
            $this->max = \count($list);
        } elseif (\is_array($list)) {
            $this->max = \count($list);
        }

        return $this;
    }

    public function setMax(int $max): self
    {
        $this->max  = $max;
        $this->list = \range(0, $max - 1);

        return $this;
    }

    public function setCallback(\Closure $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    public function setThrowBatchException(bool $throwBatchException): self
    {
        $this->throwBatchException = $throwBatchException;

        return $this;
    }

    public function getProgressBar(): ?SymfonyProgressBar
    {
        return $this->progressBar;
    }

    public function init(): bool
    {
        if ($this->max <= 0) {
            $this->helper->_(
                $this->title
                    ? "{$this->title}. Number of items is 0 or less"
                    : 'Number of items is 0 or less',
            );

            return false;
        }

        $this->progressBar = $this->createProgressBar();
        if (!$this->progressBar) {
            $this->helper->_(
                $this->title
                    ? "Working on \"<blue>{$this->title}</blue>\". Number of steps: <blue>{$this->max}</blue>."
                    : "Number of steps: <blue>{$this->max}</blue>.",
            );
        }

        return true;
    }

    public function execute(): bool
    {
        if (!$this->init()) {
            return false;
        }

        $exceptionMessages = [];
        $isSkipped         = false;

        $currentIndex = 0;

        foreach ($this->list as $stepIndex => $stepValue) {
            $this->setStep($stepIndex, $currentIndex);

            $startTime   = \microtime(true);
            $startMemory = \memory_get_usage(false);

            [$stepResult, $exceptionMessage] = $this->handleOneStep($stepValue, $stepIndex, $currentIndex);

            $this->stepMemoryDiff[] = \memory_get_usage(false) - $startMemory;
            $this->stepTimers[]     = \microtime(true) - $startTime;

            $exceptionMessages = \array_merge($exceptionMessages, (array)$exceptionMessage);

            if ($this->progressBar) {
                $errorNumbers = \count($exceptionMessages);
                $errMessage   = $errorNumbers > 0 ? "<red-bl>{$errorNumbers}</red-bl>" : '0';
                $this->progressBar->setMessage($errMessage, 'jbzoo_caught_exceptions');
            }

            if (\str_contains($stepResult, self::BREAK)) {
                $isSkipped = true;
                break;
            }

            $currentIndex++;
        }

        if ($this->progressBar) {
            if ($isSkipped) {
                $this->progressBar->display();
            } else {
                $this->progressBar->finish();
            }
        }

        self::showListOfExceptions($exceptionMessages);
        $this->helper->_('');

        return true;
    }

    /**
     * @param array|int|iterable $listOrMax
     */
    public static function run(
        $listOrMax,
        \Closure $callback,
        string $title = '',
        bool $throwBatchException = true,
        ?OutputInterface $output = null,
    ): self {
        $progress = (new self($output))
            ->setTitle($title)
            ->setCallback($callback)
            ->setThrowBatchException($throwBatchException);

        if (\is_iterable($listOrMax)) {
            $progress->setList($listOrMax);
        } else {
            $progress->setMax($listOrMax);
        }

        $progress->execute();

        return $progress;
    }

    protected function buildTemplate(): string
    {
        $progressBarLines = [];
        $footerLine       = [];
        $bar              = '[%bar%]';
        $percent          = '%percent:2s%%';
        $steps            = '(%current% / %max%)';

        if ($this->title) {
            $progressBarLines[] = "Progress of <blue>{$this->title}</blue>";
        }

        if ($this->output->isVeryVerbose()) {
            $progressBarLines[] = \implode(' ', [
                $percent,
                $steps,
                $bar,
                $this->finishIcon,
            ]);
            $footerLine['Time (pass/left/est/avg/last)'] = \implode(' / ', [
                '%jbzoo_time_elapsed:9s%',
                '<info>%jbzoo_time_remaining:8s%</info>',
                '<comment>%jbzoo_time_estimated:8s%</comment>',
                '%jbzoo_time_step_avg%',
                '%jbzoo_time_step_last%',
            ]);
            $footerLine['Memory (cur/peak/limit/leak/last)'] = \implode(' / ', [
                '%jbzoo_memory_current:8s%',
                '<comment>%jbzoo_memory_peak%</comment>',
                '%jbzoo_memory_limit%',
                '%jbzoo_memory_step_avg%',
                '%jbzoo_memory_step_last%',
            ]);
            $footerLine['Caught exceptions'] = '%jbzoo_caught_exceptions%';
        } elseif ($this->output->isVerbose()) {
            $progressBarLines[] = \implode(' ', [
                $percent,
                $steps,
                $bar,
                $this->finishIcon,
                '%jbzoo_memory_current:8s%',
            ]);

            $footerLine['Time (pass/left/est)'] = \implode(' / ', [
                '%jbzoo_time_elapsed:8s%',
                '<info>%jbzoo_time_remaining:8s%</info>',
                '%jbzoo_time_estimated%',
            ]);
            $footerLine['Caught exceptions'] = '%jbzoo_caught_exceptions%';
        } else {
            $progressBarLines[] = \implode(' ', [
                $percent,
                $steps,
                $bar,
                $this->finishIcon,
                '%jbzoo_time_elapsed:8s%<blue>/</blue>%jbzoo_time_estimated% | %jbzoo_memory_current%',
            ]);
        }

        $footerLine['Last Step Message'] = '%message%';

        return \implode("\n", $progressBarLines) . "\n" . CliRender::list($footerLine) . "\n";
    }

    /**
     * @param int|string $stepIndex
     */
    private function setStep($stepIndex, int $currentIndex): void
    {
        if ($this->progressBar) {
            $this->progressBar->setProgress($currentIndex);
            $this->progressBar->setMessage($stepIndex . ': ', 'jbzoo_current_index');
        }
    }

    /**
     * @param mixed      $stepValue
     * @param int|string $stepIndex
     */
    private function handleOneStep($stepValue, $stepIndex, int $currentIndex): array
    {
        if ($this->callback === null) {
            throw new Exception('Callback function is not defined');
        }

        $exceptionMessage = null;
        $prefixMessage    = $stepIndex === $currentIndex ? $currentIndex : "{$stepIndex}/{$currentIndex}";
        $callbackResults  = [];

        // Executing callback
        try {
            $callbackResults = (array)($this->callback)($stepValue, $stepIndex, $currentIndex);
        } catch (\Exception $exception) {
            if ($this->throwBatchException) {
                $errorMessage      = '<error>Error.</error> ' . $exception->getMessage();
                $callbackResults[] = $errorMessage;
                $exceptionMessage  = " * ({$prefixMessage}): {$errorMessage}";
            } else {
                throw $exception;
            }
        }

        // Handle status messages
        $stepResult = '';
        if (!empty($callbackResults)) {
            $stepResult = \str_replace(["\n", "\r", "\t"], ' ', \implode('; ', $callbackResults));

            if ($this->progressBar) {
                if ($stepResult !== '') {
                    if (\strlen(\strip_tags($stepResult)) > self::MAX_LINE_LENGTH) {
                        $stepResult = Str::limitChars(\strip_tags($stepResult), self::MAX_LINE_LENGTH);
                    }

                    $this->progressBar->setMessage($stepResult);
                }
            } else {
                $this->helper->_(" * ({$prefixMessage}): {$stepResult}");
            }
        } elseif (!$this->progressBar) {
            $this->helper->_(" * ({$prefixMessage}): n/a");
        }

        return [$stepResult, $exceptionMessage];
    }

    private function createProgressBar(): ?SymfonyProgressBar
    {
        if ($this->helper->isProgressBarDisabled()) {
            return null;
        }

        $this->configureProgressBar();

        $progressBar = new SymfonyProgressBar($this->output, $this->max);

        $progressBar->setBarCharacter('<green>•</green>');
        $progressBar->setEmptyBarCharacter('<yellow>_</yellow>');
        $progressBar->setProgressCharacter($this->progressIcon);
        $progressBar->setBarWidth($this->output->isVerbose() ? 70 : 40);
        $progressBar->setFormat($this->buildTemplate());

        $progressBar->setMessage('n/a');
        $progressBar->setMessage('0', 'jbzoo_caught_exceptions');
        $progressBar->setProgress(0);
        $progressBar->setOverwrite(true);

        $progressBar->setRedrawFrequency(1);
        $progressBar->minSecondsBetweenRedraws(0.5);
        $progressBar->maxSecondsBetweenRedraws(1.5);

        return $progressBar;
    }

    private static function showListOfExceptions(array $exceptionMessages): void
    {
        if (\count($exceptionMessages)) {
            $listOfErrors = \implode("\n", $exceptionMessages) . "\n";
            $listOfErrors = \str_replace('<error>Error.</error> ', '', $listOfErrors);

            throw new Exception("\n Error list:\n" . $listOfErrors);
        }
    }
}
