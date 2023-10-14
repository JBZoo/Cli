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

use JBZoo\Cli\CliCommand;

/**
 * @deprecated Use `$this->progressBar()` instead of ProgressBar::run()
 */
class ProgressBar extends ProgressBarSymfony
{
    public static function run(
        int|iterable $listOrMax,
        \Closure $callback,
        string $title = '',
        bool $throwBatchException = true,
    ): void {
        // get object of parent object where we call the static method
        $backtrace = \debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $command   = $backtrace[1]['object'] ?? null;

        if ($command instanceof CliCommand) {
            $command->progressBar($listOrMax, $callback, $title, $throwBatchException);
        }
    }
}
