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

use JBZoo\Cli\CliRender;

class CliRenderTest extends PHPUnit
{
    public function testList(): void
    {
        isSame(
            \implode("\n", [
                'Qwerty: foo',
                'Qwe   : bar',
                'Ty    : baz',
            ]) . "\n",
            CliRender::list(['qwerty' => 'foo', 'qwe' => 'bar', 'ty' => 'baz', 'False' => false]),
        );

        isSame(
            \implode("\n", [
                'Lorem ipsum dolor sit amet',
                'Qwe: bar',
                'Ty : baz',
            ]) . "\n",
            CliRender::list(['Lorem ipsum dolor sit amet', 'qwe' => 'bar', 'ty' => 'baz', 'empty' => '']),
        );

        isSame(
            \implode("\n", [
                ' * Lorem ipsum dolor',
                ' * Qwe: bar',
                ' * Ty : baz',
            ]) . "\n",
            CliRender::list(['Lorem ipsum dolor', 'qwe' => 'bar', 'not_trimmed' => '   ', 'ty' => 'baz'], '  *  '),
        );

        isSame(" * Lorem ipsum dolor sit amet\n", CliRender::list(['Lorem ipsum dolor sit amet'], '  *  '));

        isSame('', CliRender::list([], '  *  '));
        isSame('', CliRender::list([''], '  *  '));
        isSame('', CliRender::list(['', ' '], '  *  '));
    }
}
