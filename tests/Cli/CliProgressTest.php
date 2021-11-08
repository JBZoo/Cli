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
 * Class CliProgressTest
 * @package JBZoo\PHPUnit
 */
class CliProgressTest extends PHPUnit
{
    public function testNoItems()
    {
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:progress', ['test-case' => 'no-items']);
        isSame(0, $exitCode);
        isSame('no-items. Number of items is 0 or less', $stdOut);
        isSame('', $errOut);
    }

    public function testMinimal()
    {
        skip('Fix me');
        $item = Helper::executeVirtaul('test:progress', [
            'test-case'   => 'minimal',
            'stdout-only' => null
        ]);



        isSame(0, $exitCode);
        isSame('no-items. Number of items is 0 or less', $stdOut);
        isSame('', $errOut);
    }
}
