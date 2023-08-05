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
        $cmdResult = Helper::executeReal('test:output');

        isSame(0, $cmdResult->code);
        isSame(
            \implode("\n", [
                'Normal 1',
                'Normal 2',
                'Quiet -q',
                'Legacy Output: Legacy',
                'Legacy Output:    Message',
            ]),
            $cmdResult->std,
        );

        isSame(
            \implode(\PHP_EOL, [
                'Error: Message',
                'Error (e)',
                'Error: Error (error)',
                'Muted Exception: Error (exception)',
            ]),
            $cmdResult->err,
        );
    }

    public function testInfo(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['v' => null]);
        isSame(
            \implode(\PHP_EOL, [
                'Error: Message',
                'Error (e)',
                'Error: Error (error)',
                'Muted Exception: Error (exception)',
            ]),
            $cmdResult->err,
        );
        isSame(0, $cmdResult->code);

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
            $cmdResult->std,
        );

        isSame(
            Helper::executeReal('test:output', ['v' => null])->std,
            Helper::executeReal('test:output', ['verbose' => null])->std,
        );
    }

    public function testVerbose(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['-vv' => null]);
        isSame(
            \implode(\PHP_EOL, [
                'Error: Message',
                'Error (e)',
                'Error: Error (error)',
                'Muted Exception: Error (exception)',
            ]),
            $cmdResult->err,
        );
        isSame(0, $cmdResult->code);

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
            $cmdResult->std,
        );
    }

    public function testDebug(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['-vvv' => null]);
        isSame(
            \implode(\PHP_EOL, [
                'Error: Message',
                'Error (e)',
                'Error: Error (error)',
                'Muted Exception: Error (exception)',
            ]),
            $cmdResult->err,
        );
        isSame(0, $cmdResult->code);

        isContain('Debug: Working Directory is ', $cmdResult->std);
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
            $cmdResult->std,
        );

        isContain('Debug: Memory Usage/Peak:', $cmdResult->std);
        isContain('Debug: Exit Code is "0"', $cmdResult->std);
    }

    public function testQuiet(): void
    {
        isContain('Quiet -q', Helper::executeReal('test:output', ['q' => null])->std);
        isContain('Quiet -q', Helper::executeReal('test:output', ['quiet' => null])->std);
    }

    public function testProfile(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['profile' => null]);

        isContain('B] Normal 1', $cmdResult->std);
        isContain('B] Normal 2', $cmdResult->std);
        isContain('B] Quiet -q', $cmdResult->std);
        isContain('B] Memory Usage/Peak:', $cmdResult->std);
        isContain('Execution Time:', $cmdResult->std);

        $firstLine = \explode("\n", $cmdResult->std)[0];
        $lineParts = \explode('] ', $firstLine);

        isTrue(Helper::validateProfilerFormat($lineParts[0] . ']'), $firstLine);
    }

    public function testStdoutOnly(): void
    {
        // Redirect common message
        $cmdResult = Helper::executeReal('test:output', ['stdout-only' => null]);

        isSame('', $cmdResult->err);
        isSame(0, $cmdResult->code);

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
            $cmdResult->std,
        );

        // Redirect exception messsage
        $cmdResult = Helper::executeReal('test:output', ['stdout-only' => null, 'exception' => 'Some message']);
        isEmpty($cmdResult->err, $cmdResult->err);
        isSame(1, $cmdResult->code);
        isContain('  Some message  ', $cmdResult->std);
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
            $cmdResult->std,
        );

        // No redirect exception messsage
        $cmdResult = Helper::executeReal('test:output', ['exception' => 'Some message']);
        isContain('Error: Message', $cmdResult->err);
        isContain('  Some message  ', $cmdResult->err);
        isSame(1, $cmdResult->code);

        isContain(
            \implode("\n", [
                'Normal 1',
                'Normal 2',
                'Quiet -q',
                'Legacy Output: Legacy',
                'Legacy Output:    Message',
            ]),
            $cmdResult->std,
        );
    }

    public function testStrict(): void
    {
        // Redirect common message
        $cmdResult = Helper::executeReal('test:output', ['non-zero-on-error' => null]);
        isSame(
            \implode(\PHP_EOL, [
                'Error: Message',
                'Error (e)',
                'Error: Error (error)',
                'Muted Exception: Error (exception)',
            ]),
            $cmdResult->err,
        );
        isSame(1, $cmdResult->code);

        isSame(
            \implode("\n", [
                'Normal 1',
                'Normal 2',
                'Quiet -q',
                'Legacy Output: Legacy',
                'Legacy Output:    Message',
            ]),
            $cmdResult->std,
        );
    }

    public function testTimestamp(): void
    {
        // Redirect common message
        $cmdResult = Helper::executeReal('test:output', ['timestamp' => null]);
        isContain('] Error: Message', $cmdResult->err);
        isSame(0, $cmdResult->code);

        isContain('] Normal 1', $cmdResult->std);
        isContain('] Normal 2', $cmdResult->std);
        isContain('] Quiet -q', $cmdResult->std);

        $firstLine = \explode("\n", $cmdResult->std)[0];
        $lineParts = \explode(' ', $firstLine);

        isTrue(Helper::validateDateFormat($lineParts[0]), $firstLine);
    }

    public function testTypeOfVars(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['type-of-vars' => null]);
        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);
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
            $cmdResult->std,
        );
    }

    public function testMuteErrors(): void
    {
        $exceptionMessage = 'Some message ' . Str::random();

        $cmdResult = Helper::executeReal('test:output', ['exception' => $exceptionMessage]);
        isSame(1, $cmdResult->code);
        isContain($exceptionMessage, $cmdResult->err);

        $cmdResult = Helper::executeReal('test:output', ['exception' => $exceptionMessage, 'mute-errors' => null]);
        isSame(0, $cmdResult->code);
        isContain($exceptionMessage, $cmdResult->err);

        $cmdResult = Helper::executeReal(
            'test:output',
            ['exception' => $exceptionMessage, 'mute-errors' => null, 'non-zero-on-error' => null],
        );
        isSame(0, $cmdResult->code);
        isContain($exceptionMessage, $cmdResult->err);
    }

    public function testCronMode(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['cron' => null, 'exception' => 'Custom runtime error']);

        $message = (string)$cmdResult;

        isEmpty($cmdResult->err, $message);
        isSame(1, $cmdResult->code, $message);
        isSame('', $cmdResult->err, $message);

        isContain('] Normal 1', $cmdResult->std, false, $message);
        isContain('] Normal 2', $cmdResult->std, false, $message);
        isContain('] Error: Message', $cmdResult->std, false, $message);
        isContain('] Info1 -v', $cmdResult->std, false, $message);
        isContain('] Info: Info2 -v', $cmdResult->std, false, $message);
        isContain('] Verbose1 -vv', $cmdResult->std, false, $message);
        isContain('] Warning: Verbose2 -vv', $cmdResult->std, false, $message);
        isContain('] Error (e)', $cmdResult->std, false, $message);
        isContain('] Error: Error (error)', $cmdResult->std, false, $message);
        isContain('] Muted Exception: Error (exception)', $cmdResult->std, false, $message);
        isContain('] Quiet -q', $cmdResult->std, false, $message);
        isContain('] Legacy Output: Legacy', $cmdResult->std, false, $message);
        isContain('] Legacy Output:    Message', $cmdResult->std, false, $message);
        isContain('] Memory Usage/Peak:', $cmdResult->std, false, $message);

        isContain('[JBZoo\Cli\Exception]', $cmdResult->std, false, $message);
        isContain('Custom runtime error', $cmdResult->std, false, $message);
        isContain('Exception trace:', $cmdResult->std, false, $message);

        isNotContain('Debug1 -vvv', $cmdResult->std, false, $message);
        isNotContain('Message #1 -vvv', $cmdResult->std, false, $message);
        isNotContain('Message #2 -vvv', $cmdResult->std, false, $message);
    }

    public function testCronModeAlias(): void
    {
        $errMessage = 'Custom runtime error';

        $cmdResultAlias = Helper::executeReal('test:output', ['cron' => null, 'exception' => $errMessage]);
        $cmdResult      = Helper::executeReal('test:output', ['output-mode' => 'cron', 'exception' => $errMessage]);

        isSame(1, $cmdResultAlias->code);
        isSame(1, $cmdResult->code);

        isSame($cmdResultAlias->err, $cmdResult->err);
        isSame(\str_word_count($cmdResultAlias->std), \str_word_count($cmdResult->std));
        isSame(\count(\explode("\n", $cmdResultAlias->std)), \count(\explode("\n", $cmdResult->std)));
    }
}
