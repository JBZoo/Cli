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

namespace JBZoo\Cli;

/**
 * Class CliHelper
 * @package JBZoo\Cli
 */
class CliHelper
{
    /**
     * @return string
     */
    public static function getRootPath(): string
    {
        return defined('JBZOO_PATH_ROOT') ? (string)JBZOO_PATH_ROOT : '';
    }

    /**
     * @return string
     */
    public static function getBinPath(): string
    {
        return defined('JBZOO_PATH_BIN') ? (string)JBZOO_PATH_BIN : '';
    }
}
