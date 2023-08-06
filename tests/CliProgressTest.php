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

class CliProgressTest extends PHPUnit
{
    public function testMinimal(): void
    {
        $cmdResult = Helper::executeReal('test:progress', ['case' => 'minimal', 'sleep' => 1]);
        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->std);
        isNotContain('Progress of minimal', $cmdResult->err);
        isContain('0% (0 / 2) [>', $cmdResult->err);
        isContain('50% (1 / 2) [•', $cmdResult->err);
        isContain('100% (2 / 2) [•', $cmdResult->err);
        isContain('Last Step Message: n/a', $cmdResult->err);

        $cmdResult = Helper::executeReal('test:progress', ['case' => 'minimal', 'stdout-only' => null, 'sleep' => 1]);
        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);
        isContain('0% (0 / 2) [>', $cmdResult->std);
        isContain('50% (1 / 2) [•', $cmdResult->std);
        isContain('100% (2 / 2) [•', $cmdResult->std);
        isContain('Last Step Message: n/a', $cmdResult->std);
    }

    public function testMinimalVirtual(): void
    {
        $cmdResult = Helper::executeVirtaul('test:progress', ['case' => 'one-message', 'ansi' => null]);
        isContain('Progress of one-message', $cmdResult->std);
        isContain('Last Step Message: 1, 1, 1', $cmdResult->std);

        $cmdResult = Helper::executeVirtaul('test:progress', ['case' => 'array-assoc']);
        isContain('Progress of array-assoc', $cmdResult->std);
        isContain('Last Step Message: value_2, key_2, 1', $cmdResult->std);
    }

    public function testNoItems(): void
    {
        $cmdResult = Helper::executeReal('test:progress', ['case' => 'no-items-int']);
        isSame('', $cmdResult->err);
        isSame(0, $cmdResult->code);
        isSame('no-items-int. Number of items is 0 or less.', $cmdResult->std);

        $cmdResult = Helper::executeReal('test:progress', ['case' => 'no-items-array']);
        isSame('', $cmdResult->err);
        isSame(0, $cmdResult->code);
        isSame('no-items-array. Number of items is 0 or less.', $cmdResult->std);

        $cmdResult = Helper::executeReal('test:progress', ['case' => 'no-items-data']);
        isSame('', $cmdResult->err);
        isSame(0, $cmdResult->code);
        isSame('no-items-data. Number of items is 0 or less.', $cmdResult->std);
    }

    public function testProgressMessages(): void
    {
        $cmdResult = $this->exec('no-messages');
        isSame('', $cmdResult->err);
        isSame(0, $cmdResult->code);
        isSame(
            \implode("\n", [
                'Working on "no-messages". Number of steps: 3.',
                ' * (0): n/a',
                ' * (1): n/a',
                ' * (2): n/a',
            ]),
            $cmdResult->std,
        );

        $cmdResult = $this->exec('one-message');
        isSame('', $cmdResult->err);
        isSame(0, $cmdResult->code);
        isSame(
            \implode("\n", [
                'Working on "one-message". Number of steps: 3.',
                ' * (0): n/a',
                ' * (1): 1, 1, 1',
                ' * (2): n/a',
            ]),
            $cmdResult->std,
        );

        $cmdResult = $this->exec('simple-message-all');
        isSame('', $cmdResult->err);
        isSame(0, $cmdResult->code);
        isSame(
            \implode("\n", [
                'Working on "simple-message-all". Number of steps: 3.',
                ' * (0): 0, 0, 0',
                ' * (1): 1, 1, 1',
                ' * (2): 2, 2, 2',
            ]),
            $cmdResult->std,
        );

        $cmdResult = $this->exec('array-int');
        isSame('', $cmdResult->err);
        isSame(0, $cmdResult->code);
        isSame(
            \implode("\n", [
                'Working on "array-int". Number of steps: 3.',
                ' * (0): 4, 0, 0',
                ' * (1): 5, 1, 1',
                ' * (2): 6, 2, 2',
            ]),
            $cmdResult->std,
        );

        $cmdResult = $this->exec('array-string');
        isSame('', $cmdResult->err);
        isSame(0, $cmdResult->code);
        isSame(
            \implode("\n", [
                'Working on "array-string". Number of steps: 2.',
                ' * (0): qwerty, 0, 0',
                ' * (1): asdfgh, 1, 1',
            ]),
            $cmdResult->std,
        );

        $cmdResult = $this->exec('array-assoc');
        isSame('', $cmdResult->err);
        isSame(0, $cmdResult->code);
        isSame(
            \implode("\n", [
                'Working on "array-assoc". Number of steps: 2.',
                ' * (key_1/0): value_1, key_1, 0',
                ' * (key_2/1): value_2, key_2, 1',
            ]),
            $cmdResult->std,
        );

        $cmdResult = $this->exec('data');
        isSame('', $cmdResult->err);
        isSame(0, $cmdResult->code);
        isSame(
            \implode("\n", [
                'Working on "data". Number of steps: 2.',
                ' * (key_1/0): value_1, key_1, 0',
                ' * (key_2/1): value_2, key_2, 1',
            ]),
            $cmdResult->std,
        );

        $cmdResult = $this->exec('break');
        isSame('', $cmdResult->err);
        isSame(0, $cmdResult->code);
        isSame(
            \implode("\n", [
                'Working on "break". Number of steps: 3.',
                ' * (0): 0',
                ' * (1): Progress stopped',
            ]),
            $cmdResult->std,
        );

        $cmdResult = $this->exec('output-as-array');
        isSame('', $cmdResult->err);
        isSame(0, $cmdResult->code);
        isSame(
            \implode("\n", [
                'Working on "output-as-array". Number of steps: 2.',
                ' * (key_1/0): value_1; key_1; 0',
                ' * (key_2/1): value_2; key_2; 1',
            ]),
            $cmdResult->std,
        );
    }

    public function testException(): void
    {
        $cmdResult = $this->exec('exception');
        isSame(1, $cmdResult->code);
        isContain('Exception #1', $cmdResult->err);
        isSame(
            \implode("\n", [
                'Working on "exception". Number of steps: 3.',
                ' * (0): n/a',
            ]),
            $cmdResult->std,
        );

        $cmdResult = $this->exec('exception-list');
        isSame(1, $cmdResult->code);
        isContain('Exception #0', $cmdResult->err);
        isSame('Working on "exception-list". Number of steps: 10.', $cmdResult->std);
    }

    public function testBatchException(): void
    {
        $cmdResult = $this->exec('exception', ['batch-exception' => null]);
        isSame(1, $cmdResult->code);
        isContain('Error list:', $cmdResult->err);
        isContain('* (1): Exception #1', $cmdResult->err);
        isSame(
            \implode("\n", [
                'Working on "exception". Number of steps: 3.',
                ' * (0): n/a',
                ' * (1): Exception: Exception #1',
                ' * (2): n/a',
            ]),
            $cmdResult->std,
        );

        $cmdResult = $this->exec('exception-list', ['batch-exception' => null]);
        isSame(1, $cmdResult->code);
        isContain('Error list:', $cmdResult->err);
        isContain('* (0): Exception #0', $cmdResult->err);
        isContain('* (3): Exception #3', $cmdResult->err);
        isContain('* (6): Exception #6', $cmdResult->err);
        isContain('* (9): Exception #9', $cmdResult->err);
        isSame(
            \implode("\n", [
                'Working on "exception-list". Number of steps: 10.',
                ' * (0): Exception: Exception #0',
                ' * (1): n/a',
                ' * (2): n/a',
                ' * (3): Exception: Exception #3',
                ' * (4): n/a',
                ' * (5): n/a',
                ' * (6): Exception: Exception #6',
                ' * (7): n/a',
                ' * (8): n/a',
                ' * (9): Exception: Exception #9',
            ]),
            $cmdResult->std,
        );

        $cmdResult = $this->exec('exception-list', ['-b' => null, '-vv' => null], false);
        isSame(1, $cmdResult->code);
        isContain('[JBZoo\Cli\ProgressBars\Exception]', $cmdResult->err);
        isContain('Error list:', $cmdResult->err);
        isContain('* (0): Exception #0', $cmdResult->err);
        isContain('* (3): Exception #3', $cmdResult->err);
        isContain('* (6): Exception #6', $cmdResult->err);
        isContain('* (9): Exception #9', $cmdResult->err);
        isContain('Caught exceptions                : 4', $cmdResult->err);
        isContain('Last Step Message                : Exception: Exception #9', $cmdResult->err);
        isContain('Exception trace:', $cmdResult->err);
        isEmpty($cmdResult->std, $cmdResult->std);
    }

    public function testNested(): void
    {
        $cmdResult = $this->exec('nested', ['-b' => null, '-vv' => null]);

        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);
        isSame(
            \implode("\n", [
                'Working on "nested_parent". Number of steps: 3.',
                'Working on "nested_child_0". Number of steps: 4. Level: 2.',
                ' * (0): out_child_0_0',
                ' * (1): out_child_0_1',
                ' * (2): out_child_0_2',
                ' * (3): out_child_0_3',
                '',
                ' * (0): out_parent_0',
                'Working on "nested_child_1". Number of steps: 4. Level: 2.',
                ' * (0): out_child_1_0',
                ' * (1): out_child_1_1',
                ' * (2): out_child_1_2',
                ' * (3): out_child_1_3',
                '',
                ' * (1): out_parent_1',
                'Working on "nested_child_2". Number of steps: 4. Level: 2.',
                ' * (0): out_child_2_0',
                ' * (1): out_child_2_1',
                ' * (2): out_child_2_2',
                ' * (3): out_child_2_3',
                '',
                ' * (2): out_parent_2',
            ]),
            $cmdResult->std,
        );
    }

    public function testCatchMode(): void
    {
        $cmdResult = $this->exec('catch-mode');

        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);
        isSame(
            \implode("\n", [
                'Working on "catch-mode". Number of steps: 3.',
                ' * (0): Regular return 0; _(); cli(); echo',
                ' * (1): Regular return 1; _(); cli(); echo',
                ' * (2): Regular return 2; _(); cli(); echo',
            ]),
            $cmdResult->std,
        );
    }

    private function exec(string $testCase, array $addOptions = [], bool $noProgress = true): CmdResult
    {
        if ($noProgress) {
            $options['no-progress'] = null;
        }

        $options['case'] = $testCase;

        $options = \array_merge($options, $addOptions);

        return Helper::executeReal('test:progress', $options);
    }
}
