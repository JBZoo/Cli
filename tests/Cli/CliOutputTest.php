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
 * Class CliOutputTest
 * @package JBZoo\PHPUnit
 */
class CliOutputTest extends PHPUnit
{
    public function testNormal()
    {
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output');
        isSame(implode(PHP_EOL, [
            'Error: Message',
            'Error (e)',
            'Error: Error (error)',
            'Muted Exception: Error (exception)'
        ]), $errOut);
        isSame(0, $exitCode);

        isSame(implode("\n", [
            'Normal 1',
            'Normal 2',
            'Quiet -q',
            'Legacy Output: Legacy',
            'Legacy Output:    Message'
        ]), $stdOut);
    }

    public function testInfo()
    {
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', ['v' => null]);
        isSame(implode(PHP_EOL, [
            'Error: Message',
            'Error (e)',
            'Error: Error (error)',
            'Muted Exception: Error (exception)',
        ]), $errOut);
        isSame(0, $exitCode);

        isSame(implode("\n", [
            'Normal 1',
            'Normal 2',
            'Info1 -v',
            'Info: Info2 -v',
            'Quiet -q',
            'Legacy Output: Legacy',
            'Legacy Output:    Message'
        ]), $stdOut);

        isSame(
            Helper::executeReal('test:output', ['v' => null])[1],
            Helper::executeReal('test:output', ['verbose' => null])[1]
        );
    }

    public function testVerbose()
    {
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', ['-vv' => null]);
        isSame(implode(PHP_EOL, [
            'Error: Message',
            'Error (e)',
            'Error: Error (error)',
            'Muted Exception: Error (exception)',
        ]), $errOut);
        isSame(0, $exitCode);

        isSame(implode("\n", [
            'Normal 1',
            'Normal 2',

            'Info1 -v',
            'Info: Info2 -v',

            'Verbose1 -vv',
            'Warning: Verbose2 -vv',

            'Quiet -q',
            'Legacy Output: Legacy',
            'Legacy Output:    Message'
        ]), $stdOut);
    }

    public function testDebug()
    {
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', ['-vvv' => null]);
        isSame(implode(PHP_EOL, [
            'Error: Message',
            'Error (e)',
            'Error: Error (error)',
            'Muted Exception: Error (exception)',
        ]), $errOut);
        isSame(0, $exitCode);

        isContain('Debug: Working Directory is ', $stdOut);
        isContain(implode("\n", [
            'Normal 1',
            'Normal 2',

            'Info1 -v',
            'Info: Info2 -v',

            'Verbose1 -vv',
            'Warning: Verbose2 -vv',

            'Debug1 -vvv',
            'Debug: Message #1 -vvv',
            'Debug: Message #2 -vvv',

            'Quiet -q',
            'Legacy Output: Legacy',
            'Legacy Output:    Message',
            'Debug: Exit Code is "0"',
        ]), $stdOut);
    }

    public function testQuiet()
    {
        isContain('Quiet -q', Helper::executeReal('test:output', ['q' => null])[1]);
        isContain('Quiet -q', Helper::executeReal('test:output', ['quiet' => null])[1]);
    }

    public function testProfile()
    {
        $output = Helper::executeReal('test:output', ['profile' => null])[1];

        isContain('B] Normal 1', $output);
        isContain('B] Normal 2', $output);
        isContain('B] Quiet -q', $output);
        isContain('B] Memory Usage/Peak:', $output);
        isContain('Execution Time:', $output);
    }

    public function testStdoutOnly()
    {
        // Redirect common message
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', ['stdout-only' => null]);

        isSame('', $errOut);
        isSame(0, $exitCode);

        isSame(implode("\n", [
            'Normal 1',
            'Normal 2',
            'Error: Message',
            'Error (e)',
            'Error: Error (error)',
            'Muted Exception: Error (exception)',
            'Quiet -q',
            'Legacy Output: Legacy',
            'Legacy Output:    Message'
        ]), $stdOut);

        // Redirect exception messsage
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', [
            'stdout-only' => null,
            'exception'   => 'Some message'
        ]);
        isSame('', $errOut);
        isSame(1, $exitCode);
        isContain('  Some message  ', $stdOut);
        isContain(implode("\n", [
            'Normal 1',
            'Normal 2',
            'Error: Message',
            'Error (e)',
            'Error: Error (error)',
            'Muted Exception: Error (exception)',
            'Quiet -q',
            'Legacy Output: Legacy',
            'Legacy Output:    Message'
        ]), $stdOut);

        // No redirect exception messsage
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', [
            'exception' => 'Some message'
        ]);
        isContain('Error: Message', $errOut);
        isContain('  Some message  ', $errOut);
        isSame(1, $exitCode);

        isContain(implode("\n", [
            'Normal 1',
            'Normal 2',
            'Quiet -q',
            'Legacy Output: Legacy',
            'Legacy Output:    Message'
        ]), $stdOut);
    }

    public function testStrict()
    {
        // Redirect common message
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', ['non-zero-on-error' => null]);
        isSame(implode(PHP_EOL, [
            'Error: Message',
            'Error (e)',
            'Error: Error (error)',
            'Muted Exception: Error (exception)',
        ]), $errOut);
        isSame(1, $exitCode);

        isSame(implode("\n", [
            'Normal 1',
            'Normal 2',
            'Quiet -q',
            'Legacy Output: Legacy',
            'Legacy Output:    Message'
        ]), $stdOut);
    }

    public function testTimestamp()
    {
        // Redirect common message
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', ['timestamp' => null]);
        isContain('] Error: Message', $errOut);
        isSame(0, $exitCode);

        isContain('] Normal 1', $stdOut);
        isContain('] Normal 2', $stdOut);
        isContain('] Quiet -q', $stdOut);
    }

    public function testTypeOfVars()
    {
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', ['type-of-vars' => null]);
        isSame(0, $exitCode);
        isSame('', $errOut);
        isSame(implode("\n", [
            '0',
            'true',
            'false',
            'null',
            '1',
            '1',
            '-0.001',
        ]), $stdOut);
    }

    public function testMuteErrors()
    {
        $exceptionMessage = 'Some message ' . Str::random();

        [$exitCode, , $errOut] = Helper::executeReal('test:output', ['exception' => $exceptionMessage]);
        isSame(1, $exitCode);
        isContain($exceptionMessage, $errOut);

        [$exitCode, , $errOut] = Helper::executeReal(
            'test:output',
            ['exception' => $exceptionMessage, 'mute-errors' => null]
        );
        isSame(0, $exitCode);
        isContain($exceptionMessage, $errOut);

        [$exitCode, , $errOut] = Helper::executeReal(
            'test:output',
            ['exception' => $exceptionMessage, 'mute-errors' => null, 'non-zero-on-error' => null]
        );
        isSame(0, $exitCode);
        isContain($exceptionMessage, $errOut);
    }

    public function testCronMode()
    {
        [$exitCode, $stdOut, $errOut] = Helper::executeReal(
            'test:output',
            ['cron' => null, 'exception' => 'Custom runtime error']
        );

        isSame(1, $exitCode);
        isSame('', $errOut);

        isContain('] Normal 1', $stdOut);
        isContain('] Normal 2', $stdOut);
        isContain('] Error: Message', $stdOut);
        isContain('] Info1 -v', $stdOut);
        isContain('] Info: Info2 -v', $stdOut);
        isContain('] Verbose1 -vv', $stdOut);
        isContain('] Warning: Verbose2 -vv', $stdOut);
        isContain('] Error (e)', $stdOut);
        isContain('] Error: Error (error)', $stdOut);
        isContain('] Muted Exception: Error (exception)', $stdOut);
        isContain('] Quiet -q', $stdOut);
        isContain('] Legacy Output: Legacy', $stdOut);
        isContain('] Legacy Output:    Message', $stdOut);
        isContain('] Memory Usage/Peak:', $stdOut);

        isContain('[JBZoo\Cli\Exception]', $stdOut);
        isContain('Custom runtime error', $stdOut);
        isContain('Exception trace:', $stdOut);

        isNotContain('Debug1 -vvv', $stdOut);
        isNotContain('Message #1 -vvv', $stdOut);
        isNotContain('Message #2 -vvv', $stdOut);
    }
}
