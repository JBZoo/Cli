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

use JBZoo\Data\JSON;

class CliProgressLogstashTest extends PHPUnit
{
    public function testMinimal(): void
    {
        $stdOutput = $this->exec('minimal');
        isCount(3, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Number of steps: 2.'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', '(Step=1/Max=2): Empty Output'], $stdOutput[1]);
        Helper::assertLogstash(['NOTICE', '(Step=2/Max=2): Empty Output'], $stdOutput[2]);
    }

    public function testCustomMessages(): void
    {
        $stdOutput = $this->exec('one-message');
        isCount(4, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Working on "one-message". Number of steps: 3.'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', '(Step=1/Max=3): Empty Output'], $stdOutput[1]);
        Helper::assertLogstash(['NOTICE', '(Step=2/Max=3): 1, 1, 1'], $stdOutput[2]);
        Helper::assertLogstash(['NOTICE', '(Step=3/Max=3): Empty Output'], $stdOutput[3]);

        $stdOutput = $this->exec('simple-message-all');
        isCount(4, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Working on "simple-message-all". Number of steps: 3.'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', '(Step=1/Max=3): 0, 0, 0'], $stdOutput[1]);
        Helper::assertLogstash(['NOTICE', '(Step=2/Max=3): 1, 1, 1'], $stdOutput[2]);
        Helper::assertLogstash(['NOTICE', '(Step=3/Max=3): 2, 2, 2'], $stdOutput[3]);

        $stdOutput = $this->exec('array-int');
        isCount(4, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Working on "array-int". Number of steps: 3.'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', '(Step=1/Max=3): 4, 0, 0'], $stdOutput[1]);
        Helper::assertLogstash(['NOTICE', '(Step=2/Max=3): 5, 1, 1'], $stdOutput[2]);
        Helper::assertLogstash(['NOTICE', '(Step=3/Max=3): 6, 2, 2'], $stdOutput[3]);

        $stdOutput = $this->exec('array-string');
        isCount(3, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Working on "array-string". Number of steps: 2.'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', '(Step=1/Max=2): qwerty, 0, 0'], $stdOutput[1]);
        Helper::assertLogstash(['NOTICE', '(Step=2/Max=2): asdfgh, 1, 1'], $stdOutput[2]);

        $stdOutput = $this->exec('array-assoc');
        isCount(3, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Working on "array-assoc". Number of steps: 2.'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', '(Key=0/Step=1/Max=2): value_1, key_1, 0'], $stdOutput[1]);
        Helper::assertLogstash(['NOTICE', '(Key=1/Step=2/Max=2): value_2, key_2, 1'], $stdOutput[2]);

        $stdOutput = $this->exec('data');
        isCount(3, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Working on "data". Number of steps: 2.'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', '(Key=0/Step=1/Max=2): value_1, key_1, 0'], $stdOutput[1]);
        Helper::assertLogstash(['NOTICE', '(Key=1/Step=2/Max=2): value_2, key_2, 1'], $stdOutput[2]);

        $stdOutput = $this->exec('output-as-array');
        isCount(3, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Working on "output-as-array". Number of steps: 2.'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', '(Key=0/Step=1/Max=2): value_1; key_1; 0'], $stdOutput[1]);
        Helper::assertLogstash(['NOTICE', '(Key=1/Step=2/Max=2): value_2; key_2; 1'], $stdOutput[2]);
    }

    public function testBreak(): void
    {
        $stdOutput = $this->exec('break');
        isCount(3, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Working on "break". Number of steps: 3.'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', '(Step=1/Max=3): 0'], $stdOutput[1]);
        Helper::assertLogstash(['NOTICE', '(Step=2/Max=3): Progress stopped'], $stdOutput[2]);
    }

    public function testNoItems(): void
    {
        $stdOutput = $this->exec('no-items-int');
        isCount(1, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'no-items-int. Number of items is 0 or less'], $stdOutput[0]);

        $stdOutput = $this->exec('no-items-array');
        isCount(1, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'no-items-array. Number of items is 0 or less'], $stdOutput[0]);

        $stdOutput = $this->exec('no-items-data');
        isCount(1, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'no-items-data. Number of items is 0 or less'], $stdOutput[0]);
    }

    public function testException(): void
    {
        $stdOutput = $this->exec('exception', [], 1);
        isCount(3, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Working on "exception". Number of steps: 3.'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', '(Step=1/Max=3): Empty Output'], $stdOutput[1]);
        Helper::assertLogstash(['CRITICAL', 'Command Exception: Exception #1'], $stdOutput[2]);

        isSame('Exception', $stdOutput[2]->find('context.error.type'));
        isSame(0, $stdOutput[2]->find('context.error.code'));
        isSame('Exception #1', $stdOutput[2]->find('context.error.message'));
        isNotEmpty($stdOutput[2]->find('context.error.file'));
        isNotEmpty($stdOutput[2]->find('context.error.stack_trace'));
    }

    public function testExceptionList(): void
    {
        $stdOutput = $this->exec('exception-list', [], 1);
        isCount(2, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Working on "exception-list". Number of steps: 10.'], $stdOutput[0]);
        Helper::assertLogstash(['CRITICAL', 'Command Exception: Exception #0'], $stdOutput[1]);

        isSame('RuntimeException', $stdOutput[1]->find('context.error.type'));
        isSame(0, $stdOutput[1]->find('context.error.code'));
        isSame('Exception #0', $stdOutput[1]->find('context.error.message'));
        isNotEmpty($stdOutput[1]->find('context.error.file'));
        isNotEmpty($stdOutput[1]->find('context.error.stack_trace'));
    }

    public function testExceptionBatch(): void
    {
        $stdOutput = $this->exec('exception', ['batch-exception' => null], 1);
        isCount(5, $stdOutput);

        Helper::assertLogstash(['NOTICE', 'Working on "exception". Number of steps: 3.'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', '(Step=1/Max=3): Empty Output'], $stdOutput[1]);
        Helper::assertLogstash(['NOTICE', '(Step=2/Max=3): Exception: Exception #1'], $stdOutput[2]);
        Helper::assertLogstash(['NOTICE', '(Step=3/Max=3): Empty Output'], $stdOutput[3]);
        Helper::assertLogstash(
            ['CRITICAL', 'Command Exception: BatchExceptions: (Step=2/Max=3): Exception #1'],
            $stdOutput[4],
        );
    }

    public function testExceptionListBatch(): void
    {
        $stdOutput = $this->exec('exception-list', ['batch-exception' => null], 1);
        isCount(12, $stdOutput);

        Helper::assertLogstash(['NOTICE', 'Working on "exception-list". Number of steps: 10.'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', '(Step=1/Max=10): Exception: Exception #0'], $stdOutput[1]);
        Helper::assertLogstash(['NOTICE', '(Step=2/Max=10): Empty Output'], $stdOutput[2]);
        Helper::assertLogstash(['NOTICE', '(Step=3/Max=10): Empty Output'], $stdOutput[3]);
        Helper::assertLogstash(['NOTICE', '(Step=4/Max=10): Exception: Exception #3'], $stdOutput[4]);
        Helper::assertLogstash(['NOTICE', '(Step=5/Max=10): Empty Output'], $stdOutput[5]);
        Helper::assertLogstash(['NOTICE', '(Step=6/Max=10): Empty Output'], $stdOutput[6]);
        Helper::assertLogstash(['NOTICE', '(Step=7/Max=10): Exception: Exception #6'], $stdOutput[7]);
        Helper::assertLogstash(['NOTICE', '(Step=8/Max=10): Empty Output'], $stdOutput[8]);
        Helper::assertLogstash(['NOTICE', '(Step=9/Max=10): Empty Output'], $stdOutput[9]);
        Helper::assertLogstash(['NOTICE', '(Step=10/Max=10): Exception: Exception #9'], $stdOutput[10]);
        Helper::assertLogstash(
            [
                'CRITICAL',
                'Command Exception: BatchExceptions: ' .
                '(Step=1/Max=10): Exception #0; ' .
                '(Step=4/Max=10): Exception #3; ' .
                '(Step=7/Max=10): Exception #6; ' .
                '(Step=10/Max=10): Exception #9',
            ],
            $stdOutput[11],
        );
    }

    public function testNested(): void
    {
        $stdOutput = $this->exec('nested', ['-b' => null, '-vv' => null]);
        isCount(21, $stdOutput);

        Helper::assertLogstash(['INFO', 'Command Start: test:progress'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', 'Working on "nested_parent". Number of steps: 3.'], $stdOutput[1]);
        Helper::assertLogstash(['NOTICE', 'Working on "nested_child_0". Number of steps: 4.'], $stdOutput[2]);
        Helper::assertLogstash(['NOTICE', '(Step=1/Max=4): out_child_0_0'], $stdOutput[3]);
        Helper::assertLogstash(['NOTICE', '(Step=2/Max=4): out_child_0_1'], $stdOutput[4]);
        Helper::assertLogstash(['NOTICE', '(Step=3/Max=4): out_child_0_2'], $stdOutput[5]);
        Helper::assertLogstash(['NOTICE', '(Step=4/Max=4): out_child_0_3'], $stdOutput[6]);
        Helper::assertLogstash(['NOTICE', '(Step=1/Max=3): out_parent_0'], $stdOutput[7]);
        Helper::assertLogstash(['NOTICE', 'Working on "nested_child_1". Number of steps: 4.'], $stdOutput[8]);
        Helper::assertLogstash(['NOTICE', '(Step=1/Max=4): out_child_1_0'], $stdOutput[9]);
        Helper::assertLogstash(['NOTICE', '(Step=2/Max=4): out_child_1_1'], $stdOutput[10]);
        Helper::assertLogstash(['NOTICE', '(Step=3/Max=4): out_child_1_2'], $stdOutput[11]);
        Helper::assertLogstash(['NOTICE', '(Step=4/Max=4): out_child_1_3'], $stdOutput[12]);
        Helper::assertLogstash(['NOTICE', '(Step=2/Max=3): out_parent_1'], $stdOutput[13]);
        Helper::assertLogstash(['NOTICE', 'Working on "nested_child_2". Number of steps: 4.'], $stdOutput[14]);
        Helper::assertLogstash(['NOTICE', '(Step=1/Max=4): out_child_2_0'], $stdOutput[15]);
        Helper::assertLogstash(['NOTICE', '(Step=2/Max=4): out_child_2_1'], $stdOutput[16]);
        Helper::assertLogstash(['NOTICE', '(Step=3/Max=4): out_child_2_2'], $stdOutput[17]);
        Helper::assertLogstash(['NOTICE', '(Step=4/Max=4): out_child_2_3'], $stdOutput[18]);
        Helper::assertLogstash(['NOTICE', '(Step=3/Max=3): out_parent_2'], $stdOutput[19]);
        Helper::assertLogstash(['INFO', 'Command Finish: ExitCode=0'], $stdOutput[20]);
    }

    /**
     * @return JSON[]
     */
    private function exec(string $testCase, array $addOptions = [], int $excpectedExitCode = 0): array
    {
        $options = \array_merge(['output-mode' => 'logstash', 'case' => $testCase], $addOptions);

        $cmdResult = Helper::executeReal('test:progress', $options);

        $message = \print_r($cmdResult, true);

        isSame($excpectedExitCode, $cmdResult->code, $message);
        isSame('', $cmdResult->err, $message);
        isNotEmpty($cmdResult->std, $message);

        return Helper::prepareLogstash($cmdResult->std);
    }
}
