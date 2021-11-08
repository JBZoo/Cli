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

use function JBZoo\Data\json;

/**
 * Class CliMultiProcess
 * @package JBZoo\PHPUnit
 */
class CliMultiProcessTest extends PHPUnit
{
    public function testAsRealExecution()
    {
        $start = microtime(true);
        $result = Helper::executeReal('test:sleep-multi 123 " qwerty "', ['sleep' => 1], 'JBZOO_TEST_VAR=123456');

        $time = microtime(true) - $start;

        $outputAsArray = json($result[1])->getArrayCopy();

        $expectecContent = implode("\n", [
            'Sleep : 1',
            'Arg #1: 123',
            'Arg #2:  qwerty ',
            'Arg #3: QWERTY-3',
            'Env Var: 123456',
        ]);

        isSame([
            "Started: 1\n{$expectecContent}\nFinished: 1",
            "Started: 2\n{$expectecContent}\nFinished: 2",
            "Started: 3\n{$expectecContent}\nFinished: 3",
            "Started: 4\n{$expectecContent}\nFinished: 4",
            "Started: 5\n{$expectecContent}\nFinished: 5",
        ], $outputAsArray);

        isTrue($time < 5, "Total time: {$time}");
    }

    public function testAsVirtalExecution()
    {
        $start = microtime(true);
        $result = Helper::executeVirtaul('test:sleep-multi', ['sleep' => 1]);
        $time = microtime(true) - $start;

        $outputAsArray = json($result)->getArrayCopy();

        $expectecContent = implode("\n", [
            'Sleep : 1',
            'Arg #1: QWERTY-1',
            'Arg #2: QWERTY-2',
            'Arg #3: QWERTY-3',
            'Env Var: ',
        ]);

        isSame([
            "Started: 1\n{$expectecContent}\nFinished: 1",
            "Started: 2\n{$expectecContent}\nFinished: 2",
            "Started: 3\n{$expectecContent}\nFinished: 3",
            "Started: 4\n{$expectecContent}\nFinished: 4",
            "Started: 5\n{$expectecContent}\nFinished: 5",
        ], $outputAsArray);

        isTrue($time < 5);
    }
}
