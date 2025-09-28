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

namespace JBZoo\Cli\ProcessManager;

use Symfony\Component\Process\Process;

/**
 * The interface of the process manager.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface ProcessManagerInterface
{
    /**
     * Adds a process to the manager.
     */
    public function addProcess(Process $process, ?callable $callback = null, array $env = []): self;

    /**
     * Waits for all processes to be finished.
     */
    public function waitForAllProcesses(): self;

    /**
     * Returns whether the manager still has unfinished processes.
     */
    public function hasUnfinishedProcesses(): bool;
}
