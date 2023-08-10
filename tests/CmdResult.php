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

namespace JBZoo\PHPUnit;

use JBZoo\Data\JSON;

class CmdResult
{
    public int    $code;
    public string $std;
    public string $err;
    public string $cmd;

    public function __construct(int $exitCode, string $stdOut, string $stdErr, string $command)
    {
        $this->code = $exitCode;
        $this->std  = $stdOut;
        $this->err  = $stdErr;
        $this->cmd  = $command;
    }

    public function __toString(): string
    {
        return \print_r($this, true);
    }

    public function stdJson(): JSON
    {
        return new JSON($this->std);
    }

    public function errJson(): JSON
    {
        return new JSON($this->err);
    }
}
