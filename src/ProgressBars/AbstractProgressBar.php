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

namespace JBZoo\Cli\ProgressBars;

use JBZoo\Utils\Arr;
use JBZoo\Utils\Dates;
use JBZoo\Utils\FS;
use JBZoo\Utils\Stats;
use JBZoo\Utils\Sys;
use Symfony\Component\Console\Helper\Helper as SymfonyHelper;
use Symfony\Component\Console\Helper\ProgressBar as SymfonyProgressBar;

/**
 * Class AbstractProgressBar
 * @package JBZoo\Cli\ProgressBars
 */
abstract class AbstractProgressBar
{
    public const BREAK = '<yellow>Progress stopped</yellow>';

    public const MAX_LINE_LENGTH = 80;

    /**
     * @var int[]
     */
    protected $stepMemoryDiff = [];

    /**
     * @var float[]
     */
    protected $stepTimers = [];

    /**
     * @return string
     */
    abstract protected function buildTemplate(): string;

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function configureProgressBar(): void
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
}
