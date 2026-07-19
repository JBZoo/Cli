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

use JBZoo\Cli\CliApplication;

/**
 * Regression guard for issue #27: Symfony Console 8.0 removed Application::add(),
 * which CliApplication used to register commands. The fix routes registration
 * through Application::addCommands(), which exists on Symfony Console 7.3+ and 8.x.
 */
final class ConsoleCompatTest extends PHPUnit
{
    public function testRegisterCommandsWorksAcrossSymfonyConsoleMajors(): void
    {
        // Exercises the exact registration path that fataled on Console 8 with
        // "Call to undefined method JBZoo\Cli\CliApplication::add()".
        $application = new CliApplication('Console Compat', '1.0.0');
        $application->registerCommandsByPath(__DIR__ . '/TestApp/Commands', 'JBZoo\PHPUnit\TestApp');

        isTrue(\count($application->all()) > 0);
        isTrue($application->has('test:cli-options'));
        success('registerCommandsByPath registered commands without the removed add() method.');
    }
}
