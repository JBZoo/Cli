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
 * Class CliOutputTest
 * @package JBZoo\PHPUnit
 */
class CliOutputTest extends PHPUnit
{
    public function testNormal()
    {
        isSame(implode("\n", [
            'Normal 1',
            'Normal 2',
            'Error: Message',
            'Quite -q',
        ]), trim(Helper::executeReal('test:output')));
    }

    public function testInfo()
    {
        isSame(implode("\n", [
            'Normal 1',
            'Normal 2',
            'Error: Message',

            'Info1 -v',
            'Info: Info2 -v',

            'Quite -q',
        ]), trim(Helper::executeReal('test:output', ['v' => null])));

        isSame(
            trim(Helper::executeReal('test:output', ['v' => null])),
            trim(Helper::executeReal('test:output', ['verbose' => null]))
        );
    }

    public function testVerbose()
    {
        isSame(implode("\n", [
            'Normal 1',
            'Normal 2',
            'Error: Message',

            'Info1 -v',
            'Info: Info2 -v',

            'Verbose1 -vv',
            'Warn: Verbose2 -vv',

            'Quite -q',
        ]), trim(Helper::executeReal('test:output', ['-vv' => null])));
    }

    public function testDebug()
    {
        $output = trim(Helper::executeReal('test:output', ['-vvv' => null]));

        isContain(implode("\n", [
            'Normal 1',
            'Normal 2',
            'Error: Message',

            'Info1 -v',
            'Info: Info2 -v',

            'Verbose1 -vv',
            'Warn: Verbose2 -vv',

            'Debug1 -vvv',
            'Debug: Debug2 -vvv',

            'Quite -q',
        ]), $output);

        isContain(implode("\n", [
            '---- ---- ---- ---- ---- ---- ---- ---- ----',
            '* Execution Time  :',
        ]), $output);

        isContain('* Memory (cur/max):', $output);
    }

    public function testQuite()
    {
        isContain('Quite -q', trim(Helper::executeReal('test:output', ['q' => null])));
    }
}
