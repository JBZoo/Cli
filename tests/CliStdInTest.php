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

use JBZoo\Utils\Str;

class CliStdInTest extends PHPUnit
{
    public function testStdInEmpty(): void
    {
        isSame('', Helper::executeReal('test:cli-stdin')->std);
    }

    public function testStdInNotEmpty(): void
    {
        $ramdom = Str::random();
        isSame(
            "string(11) \"{$ramdom}\n\"",
            Helper::executeReal('test:cli-stdin', ['var-dump' => null], "echo \"{$ramdom}\" | ")->std,
        );
    }

    public function testStdInFile(): void
    {
        $file = __FILE__;
        isSame(
            \trim(\file_get_contents($file)),
            Helper::executeReal('test:cli-stdin', [], "cat \"{$file}\" | ")->std,
        );
    }

    public function testStdInSpaces(): void
    {
        isSame(
            "string(2) \" \n\"",
            Helper::executeReal('test:cli-stdin', ['var-dump' => null], 'echo " " | ')->std,
        );
    }
}
