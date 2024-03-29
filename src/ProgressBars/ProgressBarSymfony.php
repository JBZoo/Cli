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

use JBZoo\Cli\CliRender;
use JBZoo\Cli\Icons;
use JBZoo\Cli\OutputMods\AbstractOutputMode;
use JBZoo\Utils\Str;
use Symfony\Component\Console\Helper\ProgressBar as SymfonyProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

use function JBZoo\Utils\isStrEmpty;

class ProgressBarSymfony extends AbstractSymfonyProgressBar
{
    private const LIMIT_ITEMT_FOR_PROFILING = 10;

    private OutputInterface     $output;
    private ?SymfonyProgressBar $progressBar = null;

    private string $finishIcon;
    private string $progressIcon;

    public function __construct(AbstractOutputMode $outputMode)
    {
        parent::__construct($outputMode);

        $this->output = $outputMode->getOutput();

        $this->progressIcon = Icons::getRandomIcon(Icons::GROUP_PROGRESS, $this->output->isDecorated());
        $this->finishIcon   = Icons::getRandomIcon(Icons::GROUP_FINISH, $this->output->isDecorated());
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(): bool
    {
        if (!$this->init()) {
            return false;
        }

        $exceptionMessages = [];
        $isSkipped         = false;

        $currentIndex = 0;

        if ($this->callbackOnStart !== null) {
            \call_user_func($this->callbackOnStart, $this);
        }

        $isOptimizeMode = $this->isOptimizeMode();

        foreach ($this->list as $stepIndex => $stepValue) {
            $this->setStep($stepIndex, $currentIndex);

            $startTime   = 0;
            $startMemory = 0;
            if (!$isOptimizeMode) {
                $startTime   = \microtime(true);
                $startMemory = \memory_get_usage(false);
            }

            [$stepResult, $exceptionMessage] = $this->handleOneStep($stepValue, $stepIndex, $currentIndex);

            if (!$isOptimizeMode) {
                $this->stepMemoryDiff[] = \memory_get_usage(false) - $startMemory;
                $this->stepTimers[]     = \microtime(true) - $startTime;

                $this->stepMemoryDiff = self::sliceProfileStats($this->stepMemoryDiff);
                $this->stepTimers     = self::sliceProfileStats($this->stepTimers);
            }

            $exceptionMessages = \array_merge($exceptionMessages, (array)$exceptionMessage);

            if ($this->progressBar !== null) {
                $errorNumbers = \count($exceptionMessages);
                $errMessage   = $errorNumbers > 0 ? "<red-bl>{$errorNumbers}</red-bl>" : '0';
                $this->progressBar->setMessage($errMessage, 'jbzoo_caught_exceptions');
            }

            if (\str_contains($stepResult, ExceptionBreak::MESSAGE)) {
                $isSkipped = true;
                break;
            }

            $currentIndex++;
        }

        if ($this->progressBar !== null) {
            if ($isSkipped) {
                $this->progressBar->display();
            } else {
                $this->progressBar->finish();
            }
        }

        if ($this->callbackOnFinish !== null) {
            \call_user_func($this->callbackOnFinish, $this);
        }

        self::showListOfExceptions($exceptionMessages);

        return true;
    }

    protected function buildTemplate(): string
    {
        $progressBarLines = [];
        $footerLine       = [];

        $bar     = '[%bar%]';
        $percent = '%percent:2s%%';
        $steps   = '(%current% / %max%)';

        if (!isStrEmpty($this->title)) {
            $progressBarLines[] = "Progress of <blue>{$this->title}</blue>";
        }

        if ($this->output->isVeryVerbose()) {
            $progressBarLines[] = \implode(' ', [$percent, $steps, $bar, $this->finishIcon]);

            $footerLine['Time (pass/left/est/median/last)'] = \implode(' / ', [
                '%jbzoo_time_elapsed:9s%',
                '<info>%jbzoo_time_remaining:8s%</info>',
                '<comment>%jbzoo_time_estimated:8s%</comment>',
                '%jbzoo_time_step_median%',
                '%jbzoo_time_step_last%',
            ]);

            $footerLine['Mem (cur/peak/limit/leak/last)'] = \implode(' / ', [
                '%jbzoo_memory_current:8s%',
                '<comment>%jbzoo_memory_peak%</comment>',
                '%jbzoo_memory_limit%',
                '%jbzoo_memory_step_median%',
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

    private function init(): bool
    {
        $progresBarLevel = $this->getNestedLevel();
        $levelPostfix    = $progresBarLevel > 1 ? " Level: {$progresBarLevel}." : '';

        if ($this->max <= 0) {
            if (isStrEmpty($this->title)) {
                $this->outputMode->_("Number of items is 0 or less.{$levelPostfix}");
            } else {
                $this->outputMode->_("{$this->title}. Number of items is 0 or less.{$levelPostfix}");
            }

            return false;
        }

        $this->progressBar = $this->createProgressBar();
        if ($this->progressBar === null) {
            if (isStrEmpty($this->title)) {
                $this->outputMode->_("Number of steps: <blue>{$this->max}</blue>.{$levelPostfix}");
            } else {
                $this->outputMode->_(
                    "Working on \"<blue>{$this->title}</blue>\". " .
                    "Number of steps: <blue>{$this->max}</blue>.{$levelPostfix}",
                );
            }
        }

        return true;
    }

    private function setStep(float|int|string $stepIndex, int $currentIndex): void
    {
        if ($this->progressBar !== null) {
            $this->progressBar->setProgress($currentIndex);
            $this->progressBar->setMessage($stepIndex . ': ', 'jbzoo_current_index');
        }
    }

    private function handleOneStep(mixed $stepValue, float|int|string $stepIndex, int $currentIndex): array
    {
        if ($this->callback === null) {
            throw new Exception('Callback function is not defined');
        }

        $exceptionMessage = null;
        $prefixMessage    = $stepIndex === $currentIndex ? $currentIndex : "{$stepIndex}/{$currentIndex}";
        $callbackResults  = [];

        $this->outputMode->catchModeStart();

        // Executing callback
        try {
            $callbackResults = (array)($this->callback)($stepValue, $stepIndex, $currentIndex);
        } catch (ExceptionBreak $exception) {
            $callbackResults[] = '<yellow-bl>' . ExceptionBreak::MESSAGE . '</yellow-bl> ' . $exception->getMessage();
        } catch (\Exception $exception) {
            if ($this->throwBatchException) {
                $errorMessage      = '<error>Exception:</error> ' . $exception->getMessage();
                $callbackResults[] = $errorMessage;
                $exceptionMessage  = " * ({$prefixMessage}): {$errorMessage}";
            } else {
                throw $exception;
            }
        }

        // Collect eventual output
        $cathedMessages = $this->outputMode->catchModeFinish();
        if (\count($cathedMessages) > 0) {
            $callbackResults = \array_merge($callbackResults, $cathedMessages);
        }

        // Handle status messages
        $stepResult = '';
        if (\count($callbackResults) > 0) {
            $stepResult = \str_replace(["\n", "\r", "\t"], ' ', \implode('; ', $callbackResults));

            if ($this->progressBar !== null) {
                if (\strlen(\strip_tags($stepResult)) > self::MAX_LINE_LENGTH) {
                    $stepResult = Str::limitChars(\strip_tags($stepResult), self::MAX_LINE_LENGTH);
                }

                $this->progressBar->setMessage($stepResult);
            } else {
                $this->outputMode->_(" * ({$prefixMessage}): {$stepResult}");
            }
        } elseif ($this->progressBar === null) {
            $this->outputMode->_(" * ({$prefixMessage}): n/a");
        }

        return [$stepResult, $exceptionMessage];
    }

    private function createProgressBar(): ?SymfonyProgressBar
    {
        if ($this->outputMode->isProgressBarDisabled()) {
            return null;
        }

        $this->configureProgressBar($this->isOptimizeMode());

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

        if (!$this->isOptimizeMode()) {
            $progressBar->setRedrawFrequency(1);
            $progressBar->minSecondsBetweenRedraws(0.5);
            $progressBar->maxSecondsBetweenRedraws(1.5);
        }

        return $progressBar;
    }

    private function isOptimizeMode(): bool
    {
        return $this->outputMode->getOutput()->getVerbosity() <= OutputInterface::VERBOSITY_NORMAL;
    }

    private static function sliceProfileStats(array $arrayOfItems): array
    {
        if (\count($arrayOfItems) > self::LIMIT_ITEMT_FOR_PROFILING) {
            $arrayOfItems = \array_slice(
                $arrayOfItems,
                -self::LIMIT_ITEMT_FOR_PROFILING,
                self::LIMIT_ITEMT_FOR_PROFILING,
            );
        }

        return $arrayOfItems;
    }

    private static function showListOfExceptions(array $exceptionMessages): void
    {
        if (\count($exceptionMessages) > 0) {
            $listOfErrors = \implode("\n", $exceptionMessages) . "\n";
            $listOfErrors = \str_replace('<error>Exception:</error> ', '', $listOfErrors);

            throw new Exception("\n Error list:\n" . $listOfErrors);
        }
    }
}
