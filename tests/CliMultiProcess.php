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
 * Class CliMultiProcess
 * @package JBZoo\PHPUnit
 */
class CliMultiProcess extends PHPUnit
{
    public function testNormal()
    {
        $start = microtime(true);
        $output = Helper::executeReal('test:sleep-multi 123 " qwerty " --sleep=1 --profile');
        $time = microtime(true) - $start;

        echo $output;

        isTrue($time < 5);
    }
}
