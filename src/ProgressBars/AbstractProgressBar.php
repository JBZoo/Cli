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

use JBZoo\Cli\OutputMods\AbstractOutputMode;

abstract class AbstractProgressBar
{
    protected ?AbstractOutputMode $outputMode = null;

    protected ?\Closure $callback = null;

    protected bool $throwBatchException = true;

    protected int $max = 0;

    protected iterable $list = [];

    protected string $title = '';

    abstract public function execute(): bool;

    public function __construct(AbstractOutputMode $outputMode)
    {
        $this->outputMode = $outputMode;
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

    public function setMax(int $max): self
    {
        $this->max  = $max;
        $this->list = \range(0, $max - 1);

        return $this;
    }
}
