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

use Countable;
use JBZoo\Utils\Arr;
use JBZoo\Utils\Dates;
use JBZoo\Utils\FS;
use JBZoo\Utils\Stats;
use JBZoo\Utils\Str;
use JBZoo\Utils\Sys;
use Symfony\Component\Console\Helper\Helper as SymfonyHelper;
use Symfony\Component\Console\Helper\ProgressBar as SymfonyProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProgressBar
 * @package JBZoo\Cli
 */
class ProgressBar
{
    public const BREAK = '<yellow>Progress stopped</yellow>';

    private const MAX_LINE_LENGTH = 80;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var SymfonyProgressBar|null
     */
    private $progressBar;

    /**
     * @var iterable|array
     */
    private $list = [];

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var int
     */
    private $max = 0;

    /**
     * @var float[]
     */
    private $stepTimers = [];

    /**
     * @var int[]
     */
    private $stepMemoryDiff = [];

    /**
     * @var \Closure|null
     */
    private $callback;

    /**
     * @var bool
     */
    private $throwBatchException = true;

    /**
     * @var string
     */
    private $finishIcon;

    /**
     * @var string
     */
    private $progressIcon;

    /**
     * ProgressBar constructor.
     * @param OutputInterface|null $output
     */
    public function __construct(?OutputInterface $output = null)
    {
        $this->helper = Helper::getInstance();
        $this->output = $output ?: $this->helper->getOutput();

        $this->progressIcon = Icons::getRandomIcon(Icons::GROUP_PROGRESS, $this->output->isDecorated());
        $this->finishIcon = Icons::getRandomIcon(Icons::GROUP_FINISH, $this->output->isDecorated());
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param iterable $list
     * @return $this
     */
    public function setList(iterable $list): self
    {
        $this->list = $list;

        if ($list instanceof Countable) {
            $this->max = \count($list);
        } elseif (\is_array($list)) {
            $this->max = \count($list);
        }

        return $this;
    }

    /**
     * @param int $max
     * @return $this
     */
    public function setMax(int $max): self
    {
        $this->max = $max;
        $this->list = \range(0, $max - 1);
        return $this;
    }

    /**
     * @param \Closure $callback
     * @return $this
     */
    public function setCallback(\Closure $callback): self
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @param bool $throwBatchException
     * @return $this
     */
    public function setThrowBatchException(bool $throwBatchException): self
    {
        $this->throwBatchException = $throwBatchException;
        return $this;
    }

    /**
     * @return SymfonyProgressBar|null
     */
    public function getProgressBar(): ?SymfonyProgressBar
    {
        return $this->progressBar;
    }

    /**
     * @param iterable|array|int   $listOrMax
     * @param \Closure             $callback
     * @param string               $title
     * @param bool                 $throwBatchException
     * @param OutputInterface|null $output
     * @return ProgressBar
     */
    public static function run(
        $listOrMax,
        \Closure $callback,
        string $title = '',
        bool $throwBatchException = true,
        ?OutputInterface $output = null
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

    /**
     * @return bool
     */
    public function init(): bool
    {
        if ($this->max <= 0) {
            $this->helper->_($this->title
                ? "{$this->title}. Number of items is 0 or less"
                : "Number of items is 0 or less");

            return false;
        }

        $this->progressBar = $this->createProgressBar();
        if (!$this->progressBar) {
            $this->helper->_($this->title
                ? "Working on \"<blue>{$this->title}</blue>\". Number of steps: <blue>{$this->max}</blue>."
                : "Number of steps: <blue>{$this->max}</blue>.");
        }

        return true;
    }

    /**
     * @return bool
     */
    public function execute(): bool
    {
        if (!$this->init()) {
            return false;
        }

        $exceptionMessages = [];
        $isSkipped = false;

        $currentIndex = 0;
        foreach ($this->list as $stepIndex => $stepValue) {
            $this->setStep($stepIndex, $currentIndex);

            $startTime = \microtime(true);
            $startMemory = \memory_get_usage(false);

            [$stepResult, $exceptionMessage] = $this->handleOneStep($stepValue, $stepIndex, $currentIndex);

            $this->stepMemoryDiff[] = \memory_get_usage(false) - $startMemory;
            $this->stepTimers[] = \microtime(true) - $startTime;

            $exceptionMessages = \array_merge($exceptionMessages, (array)$exceptionMessage);

            if ($this->progressBar) {
                $errorNumbers = \count($exceptionMessages);
                $errMessage = $errorNumbers > 0 ? "<red-blink>{$errorNumbers}</red-blink>" : '0';
                $this->progressBar->setMessage($errMessage, 'jbzoo_caught_exceptions');
            }

            if (\strpos($stepResult, self::BREAK) !== false) {
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

        return true;
    }

    /**
     * @param string|int $stepIndex
     * @param int        $currentIndex
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
     * @param string|int $stepIndex
     * @param int        $currentIndex
     * @return array
     */
    private function handleOneStep($stepValue, $stepIndex, int $currentIndex): array
    {
        if (null === $this->callback) {
            throw new Exception('Callback function is not defined');
        }

        $exceptionMessage = null;
        $prefixMessage = $stepIndex === $currentIndex ? $currentIndex : "{$stepIndex}/{$currentIndex}";
        $callbackResults = [];

        // Executing callback
        try {
            $callbackResults = (array)\call_user_func($this->callback, $stepValue, $stepIndex, $currentIndex);
        } catch (\Exception $exception) {
            if ($this->throwBatchException) {
                $errorMessage = '<error>Error.</error> ' . $exception->getMessage();
                $callbackResults[] = $errorMessage;
                $exceptionMessage = " * ({$prefixMessage}): {$errorMessage}";
            } else {
                throw $exception;
            }
        }

        // Handle status messages
        $stepResult = '';
        if (!empty($callbackResults)) {
            $stepResult = \str_replace(["\n", "\r", "\t"], ' ', \implode('; ', $callbackResults));

            if ($this->progressBar) {
                if ('' !== $stepResult) {
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

    /**
     * @return SymfonyProgressBar|null
     */
    private function createProgressBar(): ?SymfonyProgressBar
    {
        if ($this->helper->getInput()->getOption('no-progress')) {
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

    /**
     * @return string
     */
    private function buildTemplate(): string
    {
        $progressBarLines = [];
        $footerLine = [];
        $bar = '[%bar%]';
        $percent = '%percent:2s%%';
        $steps = '(%current% / %max%)';

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
                '%jbzoo_memory_current:8s%'
            ]);

            $footerLine['Time (pass/left/est)'] = \implode(' / ', [
                '%jbzoo_time_elapsed:8s%',
                '<info>%jbzoo_time_remaining:8s%</info>',
                '%jbzoo_time_estimated%'
            ]);
            $footerLine['Caught exceptions'] = '%jbzoo_caught_exceptions%';
        } else {
            $progressBarLines[] = \implode(' ', [
                $percent,
                $steps,
                $bar,
                $this->finishIcon,
                '%jbzoo_time_elapsed:8s%<blue>/</blue>%jbzoo_time_estimated% | %jbzoo_memory_current%'
            ]);
        }

        $footerLine['Last Step Message'] = '%message%';

        return \implode("\n", $progressBarLines) . "\n" . Helper::renderList($footerLine) . "\n";
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function configureProgressBar(): void
    {
        static $inited;

        if ($inited) {
            return;
        }

        $inited = true;

        // Memory
        self::setPlaceholder('jbzoo_memory_current', static function (): string {
            return SymfonyHelper::formatMemory(\memory_get_usage(false));
        });

        self::setPlaceholder('jbzoo_memory_peak', static function (): string {
            return SymfonyHelper::formatMemory(\memory_get_peak_usage(false));
        });

        self::setPlaceholder('jbzoo_memory_limit', static function (): string {
            return Sys::iniGet('memory_limit');
        });

        self::setPlaceholder('jbzoo_memory_step_avg', function (SymfonyProgressBar $bar): string {
            if (!$bar->getMaxSteps() || !$bar->getProgress() || \count($this->stepMemoryDiff) === 0) {
                return 'n/a';
            }

            return FS::format((int)Stats::mean($this->stepMemoryDiff));
        });

        self::setPlaceholder('jbzoo_memory_step_last', function (SymfonyProgressBar $bar): string {
            if (!$bar->getMaxSteps() || !$bar->getProgress() || \count($this->stepMemoryDiff) === 0) {
                return 'n/a';
            }

            return FS::format(Arr::last($this->stepMemoryDiff));
        });

        // Timers
        self::setPlaceholder('jbzoo_time_elapsed', static function (SymfonyProgressBar $bar): string {
            return Dates::formatTime(\time() - $bar->getStartTime());
        });

        self::setPlaceholder('jbzoo_time_remaining', static function (SymfonyProgressBar $bar): string {
            if (!$bar->getMaxSteps()) {
                return 'n/a';
            }

            if (!$bar->getProgress()) {
                $remaining = 0;
            } else {
                $remaining = \round(
                    (\time() - $bar->getStartTime()) / $bar->getProgress() * ($bar->getMaxSteps() - $bar->getProgress())
                );
            }

            return Dates::formatTime($remaining);
        });

        self::setPlaceholder('jbzoo_time_estimated', static function (SymfonyProgressBar $bar): string {
            if (!$bar->getMaxSteps()) {
                return 'n/a';
            }

            if (!$bar->getProgress()) {
                $estimated = 0;
            } else {
                $estimated = \round((\time() - $bar->getStartTime()) / $bar->getProgress() * $bar->getMaxSteps());
            }

            return Dates::formatTime($estimated);
        });

        self::setPlaceholder('jbzoo_time_step_avg', function (SymfonyProgressBar $bar): string {
            if (!$bar->getMaxSteps() || !$bar->getProgress() || \count($this->stepTimers) === 0) {
                return 'n/a';
            }

            return \str_replace('±', '<blue>±</blue>', Stats::renderAverage($this->stepTimers)) . ' sec';
        });

        self::setPlaceholder('jbzoo_time_step_last', function (SymfonyProgressBar $bar): string {
            if (!$bar->getMaxSteps() || !$bar->getProgress() || \count($this->stepTimers) === 0) {
                return 'n/a';
            }

            return Dates::formatTime(Arr::last($this->stepTimers));
        });
    }

    /**
     * @param string   $name
     * @param callable $callable
     */
    public static function setPlaceholder(string $name, callable $callable): void
    {
        SymfonyProgressBar::setPlaceholderFormatterDefinition($name, $callable);
    }

    /**
     * @param array $exceptionMessages
     */
    private static function showListOfExceptions(array $exceptionMessages): void
    {
        if (\count($exceptionMessages)) {
            $listOfErrors = \implode("\n", $exceptionMessages) . "\n";
            $listOfErrors = \str_replace('<error>Error.</error> ', '', $listOfErrors);

            throw new Exception("\n Error list:\n" . $listOfErrors);
        }
    }
}
