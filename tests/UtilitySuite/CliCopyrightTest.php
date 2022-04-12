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

namespace JBZoo\PHPUnit;

/**
 * Class CliCopyrightTest
 *
 * @package JBZoo\PHPUnit
 */
class CliCopyrightTest extends AbstractCopyrightTest
{
    /**
     * @var string
     */
    protected $packageName     = 'Cli';
    protected $isPhpStrictType = true;
    
    /**
     * @var string[]
     */
    protected $validHeaderSH = [
        '#!/bin/bash',
        '',
        '#',
        '# _VENDOR_ - _PACKAGE_',
        '#',
        '# _DESCRIPTION_SH_',
        '#',
        '# @package    _PACKAGE_',
        '# @license    _LICENSE_',
        '# @copyright  _COPYRIGHTS_',
        '# @link       _LINK_',
        '#',
        '',
    ];
}
