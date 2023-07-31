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

class CliOutputTextTest extends PHPUnit
{
    public function testNormal(): void
    {
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output');
        isSame(
            \implode(\PHP_EOL, [
                'Error: Message',
                'Error (e)',
                'Error: Error (error)',
                'Muted Exception: Error (exception)',
            ]),
            $errOut,
        );
        isSame(0, $exitCode);

        isSame(
            \implode("\n", [
                'Normal 1',
                'Normal 2',
                'Quiet -q',
                'Legacy Output: Legacy',
                'Legacy Output:    Message',
            ]),
            $stdOut,
        );
    }

    public function testInfo(): void
    {
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', ['v' => null]);
        isSame(
            \implode(\PHP_EOL, [
                'Error: Message',
                'Error (e)',
                'Error: Error (error)',
                'Muted Exception: Error (exception)',
            ]),
            $errOut,
        );
        isSame(0, $exitCode);

        isSame(
            \implode("\n", [
                'Normal 1',
                'Normal 2',
                'Info1 -v',
                'Info: Info2 -v',
                'Quiet -q',
                'Legacy Output: Legacy',
                'Legacy Output:    Message',
            ]),
            $stdOut,
        );

        isSame(
            Helper::executeReal('test:output', ['v' => null])[1],
            Helper::executeReal('test:output', ['verbose' => null])[1],
        );
    }

    public function testVerbose(): void
    {
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', ['-vv' => null]);
        isSame(
            \implode(\PHP_EOL, [
                'Error: Message',
                'Error (e)',
                'Error: Error (error)',
                'Muted Exception: Error (exception)',
            ]),
            $errOut,
        );
        isSame(0, $exitCode);

        isSame(
            \implode("\n", [
                'Normal 1',
                'Normal 2',

                'Info1 -v',
                'Info: Info2 -v',

                'Verbose1 -vv',
                'Warning: Verbose2 -vv',

                'Quiet -q',
                'Legacy Output: Legacy',
                'Legacy Output:    Message',
            ]),
            $stdOut,
        );
    }

    public function testDebug(): void
    {
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', ['-vvv' => null]);
        isSame(
            \implode(\PHP_EOL, [
                'Error: Message',
                'Error (e)',
                'Error: Error (error)',
                'Muted Exception: Error (exception)',
            ]),
            $errOut,
        );
        isSame(0, $exitCode);

        isContain('Debug: Working Directory is ', $stdOut);
        isContain(
            \implode("\n", [
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
            ]),
            $stdOut,
        );

        isContain('Debug: Memory Usage/Peak:', $stdOut);
        isContain('Debug: Exit Code is "0"', $stdOut);
    }

    public function testQuiet(): void
    {
        isContain('Quiet -q', Helper::executeReal('test:output', ['q' => null])[1]);
        isContain('Quiet -q', Helper::executeReal('test:output', ['quiet' => null])[1]);
    }

    public function testProfile(): void
    {
        $output = Helper::executeReal('test:output', ['profile' => null])[1];

        isContain('B] Normal 1', $output);
        isContain('B] Normal 2', $output);
        isContain('B] Quiet -q', $output);
        isContain('B] Memory Usage/Peak:', $output);
        isContain('Execution Time:', $output);
    }

    public function testStdoutOnly(): void
    {
        // Redirect common message
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', ['stdout-only' => null]);

        isSame('', $errOut);
        isSame(0, $exitCode);

        isSame(
            \implode("\n", [
                'Normal 1',
                'Normal 2',
                'Error: Message',
                'Error (e)',
                'Error: Error (error)',
                'Muted Exception: Error (exception)',
                'Quiet -q',
                'Legacy Output: Legacy',
                'Legacy Output:    Message',
            ]),
            $stdOut,
        );

        // Redirect exception messsage
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', [
            'stdout-only' => null,
            'exception'   => 'Some message',
        ]);
        isSame('', $errOut);
        isSame(1, $exitCode);
        isContain('  Some message  ', $stdOut);
        isContain(
            \implode("\n", [
                'Normal 1',
                'Normal 2',
                'Error: Message',
                'Error (e)',
                'Error: Error (error)',
                'Muted Exception: Error (exception)',
                'Quiet -q',
                'Legacy Output: Legacy',
                'Legacy Output:    Message',
            ]),
            $stdOut,
        );

        // No redirect exception messsage
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', [
            'exception' => 'Some message',
        ]);
        isContain('Error: Message', $errOut);
        isContain('  Some message  ', $errOut);
        isSame(1, $exitCode);

        isContain(
            \implode("\n", [
                'Normal 1',
                'Normal 2',
                'Quiet -q',
                'Legacy Output: Legacy',
                'Legacy Output:    Message',
            ]),
            $stdOut,
        );
    }

    public function testStrict(): void
    {
        // Redirect common message
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', ['non-zero-on-error' => null]);
        isSame(
            \implode(\PHP_EOL, [
                'Error: Message',
                'Error (e)',
                'Error: Error (error)',
                'Muted Exception: Error (exception)',
            ]),
            $errOut,
        );
        isSame(1, $exitCode);

        isSame(
            \implode("\n", [
                'Normal 1',
                'Normal 2',
                'Quiet -q',
                'Legacy Output: Legacy',
                'Legacy Output:    Message',
            ]),
            $stdOut,
        );
    }

    public function testTimestamp(): void
    {
        // Redirect common message
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', ['timestamp' => null]);
        isContain('] Error: Message', $errOut);
        isSame(0, $exitCode);

        isContain('] Normal 1', $stdOut);
        isContain('] Normal 2', $stdOut);
        isContain('] Quiet -q', $stdOut);
    }

    public function testTypeOfVars(): void
    {
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:output', ['type-of-vars' => null]);
        isSame(0, $exitCode);
        isSame('', $errOut);
        isSame(
            \implode("\n", [
                '0',
                'true',
                'false',
                'null',
                '1',
                '1',
                '-0.001',
            ]),
            $stdOut,
        );
    }

    public function testMuteErrors(): void
    {
        $exceptionMessage = 'Some message ' . Str::random();

        [$exitCode, , $errOut] = Helper::executeReal('test:output', ['exception' => $exceptionMessage]);
        isSame(1, $exitCode);
        isContain($exceptionMessage, $errOut);

        [$exitCode, , $errOut] = Helper::executeReal(
            'test:output',
            ['exception' => $exceptionMessage, 'mute-errors' => null],
        );
        isSame(0, $exitCode);
        isContain($exceptionMessage, $errOut);

        [$exitCode, , $errOut] = Helper::executeReal(
            'test:output',
            ['exception' => $exceptionMessage, 'mute-errors' => null, 'non-zero-on-error' => null],
        );
        isSame(0, $exitCode);
        isContain($exceptionMessage, $errOut);
    }

    public function testCronMode(): void
    {
        $result = Helper::executeReal(
            'test:output',
            ['cron' => null, 'exception' => 'Custom runtime error'],
        );

        [$exitCode, $stdOut, $errOut] = $result;

        $message = \print_r($result, true);

        isEmpty($errOut);
        isSame(1, $exitCode, $message);
        isSame('', $errOut, $message);

        isContain('] Normal 1', $stdOut, false, $message);
        isContain('] Normal 2', $stdOut, false, $message);
        isContain('] Error: Message', $stdOut, false, $message);
        isContain('] Info1 -v', $stdOut, false, $message);
        isContain('] Info: Info2 -v', $stdOut, false, $message);
        isContain('] Verbose1 -vv', $stdOut, false, $message);
        isContain('] Warning: Verbose2 -vv', $stdOut, false, $message);
        isContain('] Error (e)', $stdOut, false, $message);
        isContain('] Error: Error (error)', $stdOut, false, $message);
        isContain('] Muted Exception: Error (exception)', $stdOut, false, $message);
        isContain('] Quiet -q', $stdOut, false, $message);
        isContain('] Legacy Output: Legacy', $stdOut, false, $message);
        isContain('] Legacy Output:    Message', $stdOut, false, $message);
        isContain('] Memory Usage/Peak:', $stdOut, false, $message);

        isContain('[JBZoo\Cli\Exception]', $stdOut, false, $message);
        isContain('Custom runtime error', $stdOut, false, $message);
        isContain('Exception trace:', $stdOut, false, $message);

        isNotContain('Debug1 -vvv', $stdOut, false, $message);
        isNotContain('Message #1 -vvv', $stdOut, false, $message);
        isNotContain('Message #2 -vvv', $stdOut, false, $message);
    }
}
