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

use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * The process manager for executing multiple processes in parallel.
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @psalm-suppress ClassMustBeFinal
 */
class ProcessManager implements ProcessManagerInterface
{
    /** The number of processes to run in parallel. */
    protected int $numberOfParallelProcesses;

    /**
     * The processes currently waiting to be executed.
     * @var array<array{Process, null|callable, array}>
     */
    protected array $pendingProcessData = [];

    /**
     * The processes currently running.
     * @var array<Process>
     */
    protected array $runningProcesses = [];

    /**
     * The callback for when a process is about to be started.
     * @var null|callable
     */
    protected $processStartCallback;

    /** The interval to wait between the polls of the processes, in milliseconds. */
    private int $pollInterval;

    /** The time to delay the start of processes to space them out, in milliseconds. */
    private int $processStartDelay;

    /**
     * The callback for when a process has finished.
     * @var null|callable
     */
    private $processFinishCallback;

    /**
     * The callback for when a process timed out.
     * @var null|callable
     */
    private $processTimeoutCallback;

    /**
     * The callback for when a process is checked.
     * @var null|callable
     */
    private $processCheckCallback;

    /**
     * @param int $numberOfParallelProcesses the number of processes to run in parallel
     * @param int $pollInterval              the interval to wait between the polls of the processes, in milliseconds
     * @param int $processStartDelay         the time to delay the start of processes to space them out, in milliseconds
     */
    public function __construct(
        int $numberOfParallelProcesses = 1,
        int $pollInterval = 100,
        int $processStartDelay = 0,
    ) {
        $this->numberOfParallelProcesses = $numberOfParallelProcesses;
        $this->pollInterval              = $pollInterval;
        $this->processStartDelay         = $processStartDelay;
    }

    /**
     * Sets the number of processes to run in parallel.
     */
    public function setNumberOfParallelProcesses(int $numberOfParallelProcesses): self
    {
        $this->numberOfParallelProcesses = $numberOfParallelProcesses;
        $this->executeNextPendingProcess(); // Start new processes in case we increased the limit.

        return $this;
    }

    /**
     * Sets the interval to wait between the polls of the processes, in milliseconds.
     */
    public function setPollInterval(int $pollInterval): self
    {
        $this->pollInterval = $pollInterval;

        return $this;
    }

    /**
     * Sets the time to delay the start of processes to space them out, in milliseconds.
     */
    public function setProcessStartDelay(int $processStartDelay): self
    {
        $this->processStartDelay = $processStartDelay;

        return $this;
    }

    /**
     * Sets the callback for when a process is about to be started.
     * @param null|callable $processStartCallback the callback, accepting a Process as only argument
     */
    public function setProcessStartCallback(?callable $processStartCallback): self
    {
        $this->processStartCallback = $processStartCallback;

        return $this;
    }

    /**
     * Sets the callback for when a process has finished.
     * @param null|callable $processFinishCallback the callback, accepting a Process as only argument
     */
    public function setProcessFinishCallback(?callable $processFinishCallback): self
    {
        $this->processFinishCallback = $processFinishCallback;

        return $this;
    }

    /**
     * Sets the callback for when a process timed out.
     */
    public function setProcessTimeoutCallback(?callable $processTimeoutCallback): self
    {
        $this->processTimeoutCallback = $processTimeoutCallback;

        return $this;
    }

    /**
     * Sets the callback for when a process is checked.
     */
    public function setProcessCheckCallback(?callable $processCheckCallback): self
    {
        $this->processCheckCallback = $processCheckCallback;

        return $this;
    }

    /**
     * Adds a process to the manager.
     */
    public function addProcess(Process $process, ?callable $callback = null, array $env = []): self
    {
        $this->pendingProcessData[] = [$process, $callback, $env];
        $this->executeNextPendingProcess();
        $this->checkRunningProcesses();

        return $this;
    }

    /**
     * Waits for all processes to be finished.
     */
    public function waitForAllProcesses(): self
    {
        while ($this->hasUnfinishedProcesses()) {
            $this->sleep($this->pollInterval);
            $this->checkRunningProcesses();
        }

        return $this;
    }

    /**
     * Returns whether the manager still has unfinished processes.
     */
    public function hasUnfinishedProcesses(): bool
    {
        return \count($this->pendingProcessData) > 0 || \count($this->runningProcesses) > 0;
    }

    /**
     * Executes the next pending process, if the limit of parallel processes is not yet reached.
     */
    protected function executeNextPendingProcess(): void
    {
        if ($this->canExecuteNextPendingRequest()) {
            $this->sleep($this->processStartDelay);

            $data = \array_shift($this->pendingProcessData);
            if ($data !== null) {
                [$process, $callback, $env] = $data;
                // @var Process $process
                $this->invokeCallback($this->processStartCallback, $process);
                $process->start($callback, $env);

                $pid = $process->getPid();
                if ($pid === null) {
                    // The process finished before we were able to check its process id.
                    $this->checkRunningProcess($pid, $process);
                } else {
                    $this->runningProcesses[$pid] = $process;
                }
            }
        }
    }

    /**
     * Checks whether a pending request is available and can be executed.
     */
    protected function canExecuteNextPendingRequest(): bool
    {
        return \count($this->runningProcesses) < $this->numberOfParallelProcesses
            && \count($this->pendingProcessData) > 0;
    }

    /**
     * Checks the running processes whether they have finished.
     */
    protected function checkRunningProcesses(): void
    {
        foreach ($this->runningProcesses as $pid => $process) {
            $this->checkRunningProcess((int)$pid, $process);
        }
    }

    /**
     * Sleeps for the specified number of milliseconds.
     * @phan-suppress PhanPluginPossiblyStaticProtectedMethod
     */
    protected function sleep(int $milliseconds): void
    {
        \usleep($milliseconds * 1000);
    }

    /**
     * Checks the process whether it has finished.
     */
    protected function checkRunningProcess(?int $pid, Process $process): void
    {
        $this->invokeCallback($this->processCheckCallback, $process);
        $this->checkProcessTimeout($process);
        if (!$process->isRunning()) {
            $this->invokeCallback($this->processFinishCallback, $process);

            if ($pid !== null) {
                unset($this->runningProcesses[$pid]);
            }
            $this->executeNextPendingProcess();
        }
    }

    /**
     * Checks whether the process already timed out.
     */
    protected function checkProcessTimeout(Process $process): void
    {
        try {
            $process->checkTimeout();
        } catch (ProcessTimedOutException) {
            $this->invokeCallback($this->processTimeoutCallback, $process);
        }
    }

    /**
     * Invokes the callback if it is an callable.
     * @phan-suppress PhanPluginPossiblyStaticProtectedMethod
     */
    protected function invokeCallback(?callable $callback, Process $process): void
    {
        if (\is_callable($callback)) {
            $callback($process);
        }
    }
}
