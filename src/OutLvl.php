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

namespace JBZoo\Cli;

class OutLvl
{
    public const Q       = 'q';
    public const DEFAULT = '';
    public const V       = 'v';
    public const VV      = 'vv';
    public const VVV     = 'vvv';
    public const E       = 'e';

    public const DEBUG     = 'debug';
    public const INFO      = 'info';
    public const WARNING   = 'warning';
    public const ERROR     = 'error';
    public const EXCEPTION = 'exception';
    public const LEGACY    = 'legacy';
}
