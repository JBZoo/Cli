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

use function JBZoo\Utils\isStrEmpty;

class ProgressBarLight extends AbstractSymfonyProgressBar
{
    public function execute(): bool
    {
        if (!$this->init()) {
            return false;
        }

        if ($this->callbackOnStart !== null) {
            \call_user_func($this->callbackOnStart, $this);
        }

        $exceptionMessages = [];
        $currentIndex      = 0;

        foreach ($this->list as $stepIndex => $stepValue) {
            [$stepResult, $exceptionMessage] = $this->handleOneStep($stepValue, $stepIndex, $currentIndex);

            $exceptionMessages = \array_merge($exceptionMessages, (array)$exceptionMessage);

            $this->outputMode->_($stepResult);

            if (\is_string($stepResult) && \str_contains($stepResult, self::BREAK)) {
                break;
            }

            $currentIndex++;
        }

        if ($this->callbackOnFinish !== null) {
            \call_user_func($this->callbackOnFinish, $this);
        }

        self::showListOfExceptions($exceptionMessages);

        return true;
    }

    private function init(): bool
    {
        $progresBarLevel = $this->getNextedLevel();
        $levelPostfix    = $progresBarLevel > 1 ? " Level: {$progresBarLevel}." : '';

        if ($this->max <= 0) {
            if (isStrEmpty($this->title)) {
                $this->outputMode->_("Number of items is 0 or less.{$levelPostfix}");
            } else {
                $this->outputMode->_("{$this->title}. Number of items is 0 or less.{$levelPostfix}");
            }

            return false;
        }

        if (isStrEmpty($this->title)) {
            $this->outputMode->_("Number of steps: {$this->max}.{$levelPostfix}");
        } else {
            $this->outputMode->_("Working on \"{$this->title}\". Number of steps: {$this->max}.{$levelPostfix}");
        }

        return true;
    }

    private function handleOneStep(mixed $stepValue, int|float|string $stepIndex, int $currentIndex): array
    {
        if ($this->callback === null) {
            throw new Exception('Callback function is not defined');
        }

        $exceptionMessage = null;

        $humanIndex    = $currentIndex + 1;
        $prefixMessage = $stepIndex === $currentIndex
            ? "Step={$humanIndex}/Max={$this->max}"
            : "Key={$currentIndex}/Step={$humanIndex}/Max={$this->max}";

        $callbackResults = [];

        $this->outputMode->catchModeStart();

        try {
            $callbackResults = (array)($this->callback)($stepValue, $stepIndex, $currentIndex);
        } catch (\Exception $exception) {
            if ($this->throwBatchException) {
                $errorMessage      = 'Exception: ' . $exception->getMessage();
                $callbackResults[] = $errorMessage;
                $exceptionMessage  = "({$prefixMessage}): {$errorMessage}";
            } else {
                throw $exception;
            }
        }

        $cathedMessages = $this->outputMode->catchModeFinish();
        if (\count($cathedMessages) > 0) {
            $callbackResults = \array_merge($callbackResults, $cathedMessages);
        }

        // Handle status messages
        if (\count($callbackResults) > 0) {
            $stepResult = "({$prefixMessage}): " . \implode('; ', $callbackResults);
        } else {
            $stepResult = "({$prefixMessage}): Empty Output";
        }

        return [$stepResult, $exceptionMessage];
    }

    private static function showListOfExceptions(array $exceptionMessages): void
    {
        if (\count($exceptionMessages) > 0) {
            $listOfErrors = \implode('; ', $exceptionMessages);
            $listOfErrors = \str_replace('Exception: ', '', $listOfErrors);

            throw new Exception('BatchExceptions: ' . $listOfErrors);
        }
    }
}
