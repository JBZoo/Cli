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

use JBZoo\Cli\Cli;

/**
 * Class CliTest
 * @package JBZoo\PHPUnit
 */
class CliTest extends PHPUnit
{
    public function testShouldDoSomeStreetMagic()
    {
        $obj = new Cli();
        isSame('street magic', $obj->doSomeStreetMagic());
    }
}
