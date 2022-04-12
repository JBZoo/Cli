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
 * Shortcut method
 * @param array|mixed $messages
 * @param string      $verboseLevel
 * @return void
 */
function cli($messages = '', string $verboseLevel = OutLvl::DEFAULT): void
{
    Cli::getInstance()->_($messages, $verboseLevel);
}
