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
    public function testMinimal()
    {
        $output = Helper::executeReal('test:progress', ['case' => 'minimal']);
        isSame(0, $output[0]);
        isSame('', $output[1]);
        isContain('Progress of minimal', $output[2]);
        isContain('0% (0 / 2) [>', $output[2]);
        isContain('50% (1 / 2) [•', $output[2]);
        isContain('100% (2 / 2) [•', $output[2]);
        isContain('Last Step Message: n/a', $output[2]);


        $output = Helper::executeReal('test:progress', ['case' => 'minimal', 'stdout-only' => null]);
        isSame(0, $output[0]);
        isSame('', $output[2]);
        isContain('0% (0 / 2) [>', $output[1]);
        isContain('50% (1 / 2) [•', $output[1]);
        isContain('100% (2 / 2) [•', $output[1]);
        isContain('Last Step Message: n/a', $output[1]);
    }

    public function testMinimalVirtual()
    {
        $output = Helper::executeVirtaul('test:progress', ['case' => 'minimal']);
        isContain('Progress of minimal', $output);
    }

    public function testNoItems()
    {
        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:progress', ['case' => 'no-items-int']);
        isSame('', $errOut);
        isSame(0, $exitCode);
        isSame('no-items-int. Number of items is 0 or less', $stdOut);

        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:progress', ['case' => 'no-items-array']);
        isSame('', $errOut);
        isSame(0, $exitCode);
        isSame('no-items-array. Number of items is 0 or less', $stdOut);

        [$exitCode, $stdOut, $errOut] = Helper::executeReal('test:progress', ['case' => 'no-items-data']);
        isSame('', $errOut);
        isSame(0, $exitCode);
        isSame('no-items-data. Number of items is 0 or less', $stdOut);
    }

    public function testProgressMessages()
    {
        [$exitCode, $stdOut, $errOut] = $this->exec('no-messages');
        isSame('', $errOut);
        isSame(0, $exitCode);
        isSame(implode("\n", [
            'Working on "no-messages". Number of steps: 3.',
            ' * (0): n/a',
            ' * (1): n/a',
            ' * (2): n/a',
        ]), $stdOut);


        [$exitCode, $stdOut, $errOut] = $this->exec('one-message');
        isSame('', $errOut);
        isSame(0, $exitCode);
        isSame(implode("\n", [
            'Working on "one-message". Number of steps: 3.',
            ' * (0): n/a',
            ' * (1): 1, 1, 1',
            ' * (2): n/a',
        ]), $stdOut);


        [$exitCode, $stdOut, $errOut] = $this->exec('simple-message-all');
        isSame('', $errOut);
        isSame(0, $exitCode);
        isSame(implode("\n", [
            'Working on "simple-message-all". Number of steps: 3.',
            ' * (0): 0, 0, 0',
            ' * (1): 1, 1, 1',
            ' * (2): 2, 2, 2',
        ]), $stdOut);


        [$exitCode, $stdOut, $errOut] = $this->exec('array-int');
        isSame('', $errOut);
        isSame(0, $exitCode);
        isSame(implode("\n", [
            'Working on "array-int". Number of steps: 3.',
            ' * (0): 4, 0, 0',
            ' * (1): 5, 1, 1',
            ' * (2): 6, 2, 2',
        ]), $stdOut);


        [$exitCode, $stdOut, $errOut] = $this->exec('array-string');
        isSame('', $errOut);
        isSame(0, $exitCode);
        isSame(implode("\n", [
            'Working on "array-string". Number of steps: 2.',
            ' * (0): qwerty, 0, 0',
            ' * (1): asdfgh, 1, 1',
        ]), $stdOut);


        [$exitCode, $stdOut, $errOut] = $this->exec('array-assoc');
        isSame('', $errOut);
        isSame(0, $exitCode);
        isSame(implode("\n", [
            'Working on "array-assoc". Number of steps: 2.',
            ' * (key_1/0): value_1, key_1, 0',
            ' * (key_2/1): value_2, key_2, 1',
        ]), $stdOut);


        [$exitCode, $stdOut, $errOut] = $this->exec('data');
        isSame('', $errOut);
        isSame(0, $exitCode);
        isSame(implode("\n", [
            'Working on "data". Number of steps: 2.',
            ' * (key_1/0): value_1, key_1, 0',
            ' * (key_2/1): value_2, key_2, 1',
        ]), $stdOut);


        [$exitCode, $stdOut, $errOut] = $this->exec('break');
        isSame('', $errOut);
        isSame(0, $exitCode);
        isSame(implode("\n", [
            'Working on "break". Number of steps: 3.',
            ' * (0): 0',
            ' * (1): Progress stopped',
        ]), $stdOut);
    }

    public function testException()
    {
        [$exitCode, $stdOut, $errOut] = $this->exec('exception');
        isSame(1, $exitCode);
        isContain('Exception #1', $errOut);
        isSame(implode("\n", [
            'Working on "exception". Number of steps: 3.',
            ' * (0): n/a',
        ]), $stdOut);
    }

    /**
     * @depends testException
     */
    public function testBatchException()
    {
        [$exitCode, $stdOut, $errOut] = $this->exec('exception', ['batch-exception' => null]);

        isSame(1, $exitCode);

        isContain('Error list:', $errOut);
        isContain('* (1): Exception #1', $errOut);

        isSame(implode("\n", [
            'Working on "exception". Number of steps: 3.',
            ' * (0): n/a',
            ' * (1): Error. Exception #1',
            ' * (2): n/a',
        ]), $stdOut);
    }

    /**
     * @param string $testCase
     * @param array  $addOptions
     * @return array
     */
    private function exec(string $testCase, array $addOptions = []): array
    {
        $options['no-progress'] = null;
        $options['case'] = $testCase;
        $options = array_merge($options, $addOptions);

        return Helper::executeReal('test:progress', $options);
    }
}
