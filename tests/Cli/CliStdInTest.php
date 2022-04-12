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

use JBZoo\Utils\Str;

/**
 * Class CliStdInTest
 * @package JBZoo\PHPUnit
 */
class CliStdInTest extends PHPUnit
{
    public function testStdInEmpty()
    {
        isSame('', Helper::executeReal('test:cli-stdin')[1]);
    }

    public function testStdInNotEmpty()
    {
        $ramdom = Str::random();
        isSame(
            "string(11) \"{$ramdom}\n\"",
            Helper::executeReal('test:cli-stdin', ['var-dump' => null], "echo \"{$ramdom}\" | ")[1]
        );
    }

    public function testStdInFile()
    {
        $file = __FILE__;
        isSame(
            trim(file_get_contents($file)),
            Helper::executeReal('test:cli-stdin', [], "cat \"{$file}\" | ")[1]
        );
    }

    public function testStdInSpaces()
    {
        isSame(
            "string(2) \" \n\"",
            Helper::executeReal('test:cli-stdin', ['var-dump' => null], 'echo " " | ')[1]
        );
    }
}
