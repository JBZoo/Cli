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

class CliOutputLogstashTest extends PHPUnit
{
    public function testFormatOfMessage(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['output-mode' => 'logstash']);
        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);

        $lines        = Helper::prepareLogstash($cmdResult->std);
        $lineAsArray  = $lines[0]->getArrayCopy();
        $lineStruture = self::replaceValues($lineAsArray);

        isCount(9, $lines);
        isSame([
            '@timestamp'    => 'string',
            '@version'      => 'integer',
            'host'          => 'string',
            'message'       => 'string',
            'type'          => 'string',
            'channel'       => 'string',
            'level'         => 'string',
            'monolog_level' => 'integer',
            'context'       => [
                'trace'   => ['id' => 'string'],
                'profile' => [
                    'memory_usage_real' => 'integer',
                    'memory_usage'      => 'integer',
                    'memory_usage_diff' => 'integer',
                    'memory_pick_real'  => 'integer',
                    'memory_pick'       => 'integer',
                    'time_total_ms'     => 'double',
                    'time_diff_ms'      => 'double',
                ],
            ],
        ], $lineStruture);
    }

    public function testFormatOfMessageVerboseFisrt(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['output-mode' => 'logstash', '-vvv' => null]);
        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);

        $lines        = Helper::prepareLogstash($cmdResult->std);
        $lineAsArray  = $lines[0]->getArrayCopy();
        $lineStruture = self::replaceValues($lineAsArray);

        isCount(19, $lines);
        isSame([
            '@timestamp'    => 'string',
            '@version'      => 'integer',
            'host'          => 'string',
            'message'       => 'string',
            'type'          => 'string',
            'channel'       => 'string',
            'level'         => 'string',
            'monolog_level' => 'integer',
            'context'       => [
                'trace'   => ['id' => 'string'],
                'profile' => [
                    'memory_usage_real' => 'integer',
                    'memory_usage'      => 'integer',
                    'memory_usage_diff' => 'integer',
                    'memory_pick_real'  => 'integer',
                    'memory_pick'       => 'integer',
                    'time_total_ms'     => 'double',
                    'time_diff_ms'      => 'double',
                ],
                'service' => [
                    'name'        => 'string',
                    'version'     => 'string',
                    'type'        => 'string',
                    'php_version' => 'string',
                ],
                'process' => [
                    'pid'               => 'integer',
                    'executable'        => 'string',
                    'command_line'      => 'string',
                    'process_command'   => 'string',
                    'working_directory' => 'string',
                ],
            ],
        ], $lineStruture);
    }

    public function testFormatOfMessageVerboseLast(): void
    {
        $cmdResult    = Helper::executeReal('test:output', ['output-mode' => 'logstash', '-vvv' => null]);
        $lineAsArray  = Helper::prepareLogstash($cmdResult->std)[18]->getArrayCopy();
        $lineStruture = self::replaceValues($lineAsArray);

        isSame([
            '@timestamp'    => 'string',
            '@version'      => 'integer',
            'host'          => 'string',
            'message'       => 'string',
            'type'          => 'string',
            'channel'       => 'string',
            'level'         => 'string',
            'monolog_level' => 'integer',
            'context'       => [
                'trace'   => ['id' => 'string'],
                'profile' => [
                    'memory_usage_real' => 'integer',
                    'memory_usage'      => 'integer',
                    'memory_usage_diff' => 'integer',
                    'memory_pick_real'  => 'integer',
                    'memory_pick'       => 'integer',
                    'time_total_ms'     => 'double',
                    'time_diff_ms'      => 'double',
                ],
                'process' => ['exit_code' => 'integer'],
            ],
        ], $lineStruture);
    }

    public function testFormatOfMessageException(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['output-mode' => 'logstash', 'exception' => 'Some message']);

        $lineAsArray  = Helper::prepareLogstash($cmdResult->std)[9]->getArrayCopy();
        $lineStruture = self::replaceValues($lineAsArray);

        isSame([
            '@timestamp'    => 'string',
            '@version'      => 'integer',
            'host'          => 'string',
            'message'       => 'string',
            'type'          => 'string',
            'channel'       => 'string',
            'level'         => 'string',
            'monolog_level' => 'integer',
            'context'       => [
                'trace'   => ['id' => 'string'],
                'profile' => [
                    'memory_usage_real' => 'integer',
                    'memory_usage'      => 'integer',
                    'memory_usage_diff' => 'integer',
                    'memory_pick_real'  => 'integer',
                    'memory_pick'       => 'integer',
                    'time_total_ms'     => 'double',
                    'time_diff_ms'      => 'double',
                ],
                'error' => [
                    'type'        => 'string',
                    'code'        => 'integer',
                    'message'     => 'string',
                    'file'        => 'string',
                    'stack_trace' => 'string',
                ],
            ],
        ], $lineStruture);
    }

    public function testNormal(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['output-mode' => 'logstash']);
        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);

        $stdOutput = Helper::prepareLogstash($cmdResult->std);
        isCount(9, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Normal 1'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', 'Normal 2'], $stdOutput[1]);
        Helper::assertLogstash(['ERROR', 'Message'], $stdOutput[2]);
        Helper::assertLogstash(['WARNING', 'Verbose2 -vv'], $stdOutput[3]);
        Helper::assertLogstash(['ERROR', 'Error (e)'], $stdOutput[4]);
        Helper::assertLogstash(['ERROR', 'Error (error)'], $stdOutput[5]);
        Helper::assertLogstash(['CRITICAL', 'Error (exception)'], $stdOutput[6]);
        Helper::assertLogstash(['WARNING', 'Legacy'], $stdOutput[7]);
        Helper::assertLogstash(['WARNING', '   Message'], $stdOutput[8]);
    }

    public function testInfo(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['output-mode' => 'logstash', '-v' => null]);
        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);

        $stdOutput = Helper::prepareLogstash($cmdResult->std);
        isCount(15, $stdOutput);
        Helper::assertLogstash(['INFO', 'Command Start: test:output'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', 'Normal 1'], $stdOutput[1]);
        Helper::assertLogstash(['NOTICE', 'Normal 2'], $stdOutput[2]);
        Helper::assertLogstash(['ERROR', 'Message'], $stdOutput[3]);
        Helper::assertLogstash(['INFO', 'Info1 -v'], $stdOutput[4]);
        Helper::assertLogstash(['INFO', 'Info2 -v'], $stdOutput[5]);
        Helper::assertLogstash(['INFO', 'Verbose1 -vv'], $stdOutput[6]);
        Helper::assertLogstash(['WARNING', 'Verbose2 -vv'], $stdOutput[7]);
        Helper::assertLogstash(['ERROR', 'Error (e)'], $stdOutput[8]);
        Helper::assertLogstash(['ERROR', 'Error (error)'], $stdOutput[9]);
        Helper::assertLogstash(['CRITICAL', 'Error (exception)'], $stdOutput[10]);
        Helper::assertLogstash(['INFO', 'Quiet -q'], $stdOutput[11]);
        Helper::assertLogstash(['WARNING', 'Legacy'], $stdOutput[12]);
        Helper::assertLogstash(['WARNING', '   Message'], $stdOutput[13]);
        Helper::assertLogstash(['INFO', 'Command Finish: ExitCode=0'], $stdOutput[14]);

        isSame(
            Helper::executeReal('test:output', ['v' => null])->std,
            Helper::executeReal('test:output', ['verbose' => null])->std,
        );
    }

    public function testVerbose(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['output-mode' => 'logstash', '-vv' => null]);
        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);

        $stdOutput = Helper::prepareLogstash($cmdResult->std);
        isCount(15, $stdOutput);
        Helper::assertLogstash(['INFO', 'Command Start: test:output'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', 'Normal 1'], $stdOutput[1]);
        Helper::assertLogstash(['NOTICE', 'Normal 2'], $stdOutput[2]);
        Helper::assertLogstash(['ERROR', 'Message'], $stdOutput[3]);
        Helper::assertLogstash(['INFO', 'Info1 -v'], $stdOutput[4]);
        Helper::assertLogstash(['INFO', 'Info2 -v'], $stdOutput[5]);
        Helper::assertLogstash(['INFO', 'Verbose1 -vv'], $stdOutput[6]);
        Helper::assertLogstash(['WARNING', 'Verbose2 -vv'], $stdOutput[7]);
        Helper::assertLogstash(['ERROR', 'Error (e)'], $stdOutput[8]);
        Helper::assertLogstash(['ERROR', 'Error (error)'], $stdOutput[9]);
        Helper::assertLogstash(['CRITICAL', 'Error (exception)'], $stdOutput[10]);
        Helper::assertLogstash(['INFO', 'Quiet -q'], $stdOutput[11]);
        Helper::assertLogstash(['WARNING', 'Legacy'], $stdOutput[12]);
        Helper::assertLogstash(['WARNING', '   Message'], $stdOutput[13]);
        Helper::assertLogstash(['INFO', 'Command Finish: ExitCode=0'], $stdOutput[14]);
    }

    public function testDebug(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['output-mode' => 'logstash', '-vvv' => null]);
        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);

        $stdOutput = Helper::prepareLogstash($cmdResult->std);
        isCount(19, $stdOutput);
        Helper::assertLogstash(['INFO', 'Command Start: test:output'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', 'Normal 1'], $stdOutput[1]);
        Helper::assertLogstash(['NOTICE', 'Normal 2'], $stdOutput[2]);
        Helper::assertLogstash(['ERROR', 'Message'], $stdOutput[3]);
        Helper::assertLogstash(['INFO', 'Info1 -v'], $stdOutput[4]);
        Helper::assertLogstash(['INFO', 'Info2 -v'], $stdOutput[5]);
        Helper::assertLogstash(['INFO', 'Verbose1 -vv'], $stdOutput[6]);
        Helper::assertLogstash(['WARNING', 'Verbose2 -vv'], $stdOutput[7]);
        Helper::assertLogstash(['DEBUG', 'Debug1 -vvv'], $stdOutput[8]);
        Helper::assertLogstash(['DEBUG', 'Message #1 -vvv'], $stdOutput[9]);
        Helper::assertLogstash(['DEBUG', 'Message #2 -vvv'], $stdOutput[10]);
        Helper::assertLogstash(['ERROR', 'Error (e)'], $stdOutput[11]);
        Helper::assertLogstash(['ERROR', 'Error (error)'], $stdOutput[12]);
        Helper::assertLogstash(['CRITICAL', 'Error (exception)'], $stdOutput[13]);
        Helper::assertLogstash(['DEBUG', 'Message with context'], $stdOutput[14]);
        Helper::assertLogstash(['INFO', 'Quiet -q'], $stdOutput[15]);
        Helper::assertLogstash(['WARNING', 'Legacy'], $stdOutput[16]);
        Helper::assertLogstash(['WARNING', '   Message'], $stdOutput[17]);
        Helper::assertLogstash(['INFO', 'Command Finish: ExitCode=0'], $stdOutput[18]);
    }

    public function testQuite(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['output-mode' => 'logstash', '-q' => null]);
        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);

        $stdOutput = Helper::prepareLogstash($cmdResult->std);
        isCount(15, $stdOutput);
        Helper::assertLogstash(['INFO', 'Command Start: test:output'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', 'Normal 1'], $stdOutput[1]);
        Helper::assertLogstash(['NOTICE', 'Normal 2'], $stdOutput[2]);
        Helper::assertLogstash(['ERROR', 'Message'], $stdOutput[3]);
        Helper::assertLogstash(['INFO', 'Info1 -v'], $stdOutput[4]);
        Helper::assertLogstash(['INFO', 'Info2 -v'], $stdOutput[5]);
        Helper::assertLogstash(['INFO', 'Verbose1 -vv'], $stdOutput[6]);
        Helper::assertLogstash(['WARNING', 'Verbose2 -vv'], $stdOutput[7]);
        Helper::assertLogstash(['ERROR', 'Error (e)'], $stdOutput[8]);
        Helper::assertLogstash(['ERROR', 'Error (error)'], $stdOutput[9]);
        Helper::assertLogstash(['CRITICAL', 'Error (exception)'], $stdOutput[10]);
        Helper::assertLogstash(['INFO', 'Quiet -q'], $stdOutput[11]);
        Helper::assertLogstash(['WARNING', 'Legacy'], $stdOutput[12]);
        Helper::assertLogstash(['WARNING', '   Message'], $stdOutput[13]);
        Helper::assertLogstash(['INFO', 'Command Finish: ExitCode=0'], $stdOutput[14]);
    }

    public function testProfile(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['output-mode' => 'logstash', 'profile' => null]);
        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);

        $stdOutput = Helper::prepareLogstash($cmdResult->std);
        isCount(9, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Normal 1'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', 'Normal 2'], $stdOutput[1]);
        Helper::assertLogstash(['ERROR', 'Message'], $stdOutput[2]);
        Helper::assertLogstash(['WARNING', 'Verbose2 -vv'], $stdOutput[3]);
        Helper::assertLogstash(['ERROR', 'Error (e)'], $stdOutput[4]);
        Helper::assertLogstash(['ERROR', 'Error (error)'], $stdOutput[5]);
        Helper::assertLogstash(['CRITICAL', 'Error (exception)'], $stdOutput[6]);
        Helper::assertLogstash(['WARNING', 'Legacy'], $stdOutput[7]);
        Helper::assertLogstash(['WARNING', '   Message'], $stdOutput[8]);
    }

    public function testStdoutOnly(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['output-mode' => 'logstash', 'stdout-only' => null]);
        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);

        $stdOutput = Helper::prepareLogstash($cmdResult->std);
        isCount(9, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Normal 1'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', 'Normal 2'], $stdOutput[1]);
        Helper::assertLogstash(['ERROR', 'Message'], $stdOutput[2]);
        Helper::assertLogstash(['WARNING', 'Verbose2 -vv'], $stdOutput[3]);
        Helper::assertLogstash(['ERROR', 'Error (e)'], $stdOutput[4]);
        Helper::assertLogstash(['ERROR', 'Error (error)'], $stdOutput[5]);
        Helper::assertLogstash(['CRITICAL', 'Error (exception)'], $stdOutput[6]);
        Helper::assertLogstash(['WARNING', 'Legacy'], $stdOutput[7]);
        Helper::assertLogstash(['WARNING', '   Message'], $stdOutput[8]);
    }

    public function testNonZeroOnError(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['output-mode' => 'logstash', 'non-zero-on-error' => null]);
        isSame(1, $cmdResult->code);
        isSame('', $cmdResult->err);

        $stdOutput = Helper::prepareLogstash($cmdResult->std);
        isCount(9, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Normal 1'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', 'Normal 2'], $stdOutput[1]);
        Helper::assertLogstash(['ERROR', 'Message'], $stdOutput[2]);
        Helper::assertLogstash(['WARNING', 'Verbose2 -vv'], $stdOutput[3]);
        Helper::assertLogstash(['ERROR', 'Error (e)'], $stdOutput[4]);
        Helper::assertLogstash(['ERROR', 'Error (error)'], $stdOutput[5]);
        Helper::assertLogstash(['CRITICAL', 'Error (exception)'], $stdOutput[6]);
        Helper::assertLogstash(['WARNING', 'Legacy'], $stdOutput[7]);
        Helper::assertLogstash(['WARNING', '   Message'], $stdOutput[8]);
    }

    public function testException(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['output-mode' => 'logstash', 'exception' => 'Some message']);
        isSame(1, $cmdResult->code);
        isSame('', $cmdResult->err);

        $stdOutput = Helper::prepareLogstash($cmdResult->std);
        isCount(10, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Normal 1'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', 'Normal 2'], $stdOutput[1]);
        Helper::assertLogstash(['ERROR', 'Message'], $stdOutput[2]);
        Helper::assertLogstash(['WARNING', 'Verbose2 -vv'], $stdOutput[3]);
        Helper::assertLogstash(['ERROR', 'Error (e)'], $stdOutput[4]);
        Helper::assertLogstash(['ERROR', 'Error (error)'], $stdOutput[5]);
        Helper::assertLogstash(['CRITICAL', 'Error (exception)'], $stdOutput[6]);
        Helper::assertLogstash(['WARNING', 'Legacy'], $stdOutput[7]);
        Helper::assertLogstash(['WARNING', '   Message'], $stdOutput[8]);
        Helper::assertLogstash(['CRITICAL', 'Command Exception: Some message'], $stdOutput[9]);
    }

    public function testTimestamp(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['output-mode' => 'logstash', 'timestamp' => null]);
        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);

        $stdOutput = Helper::prepareLogstash($cmdResult->std);
        isCount(9, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Normal 1'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', 'Normal 2'], $stdOutput[1]);
        Helper::assertLogstash(['ERROR', 'Message'], $stdOutput[2]);
        Helper::assertLogstash(['WARNING', 'Verbose2 -vv'], $stdOutput[3]);
        Helper::assertLogstash(['ERROR', 'Error (e)'], $stdOutput[4]);
        Helper::assertLogstash(['ERROR', 'Error (error)'], $stdOutput[5]);
        Helper::assertLogstash(['CRITICAL', 'Error (exception)'], $stdOutput[6]);
        Helper::assertLogstash(['WARNING', 'Legacy'], $stdOutput[7]);
        Helper::assertLogstash(['WARNING', '   Message'], $stdOutput[8]);
    }

    public function testTypeOfVars(): void
    {
        $cmdResult = Helper::executeReal('test:output', ['output-mode' => 'logstash', 'type-of-vars' => null]);
        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);

        $stdOutput = Helper::prepareLogstash($cmdResult->std);
        isCount(8, $stdOutput);
        Helper::assertLogstash(['NOTICE', ' '], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', '0'], $stdOutput[1]);
        Helper::assertLogstash(['NOTICE', 'true'], $stdOutput[2]);
        Helper::assertLogstash(['NOTICE', 'false'], $stdOutput[3]);
        Helper::assertLogstash(['NOTICE', 'null'], $stdOutput[4]);
        Helper::assertLogstash(['NOTICE', '1'], $stdOutput[5]);
        Helper::assertLogstash(['NOTICE', '1'], $stdOutput[6]);
        Helper::assertLogstash(['NOTICE', '-0.001'], $stdOutput[7]);
    }

    public function testMuteErrors(): void
    {
        $cmdResult = Helper::executeReal(
            'test:output',
            ['output-mode' => 'logstash', 'exception' => 'Some message', 'mute-errors' => null],
        );
        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);

        $stdOutput = Helper::prepareLogstash($cmdResult->std);
        isCount(10, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Normal 1'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', 'Normal 2'], $stdOutput[1]);
        Helper::assertLogstash(['ERROR', 'Message'], $stdOutput[2]);
        Helper::assertLogstash(['WARNING', 'Verbose2 -vv'], $stdOutput[3]);
        Helper::assertLogstash(['ERROR', 'Error (e)'], $stdOutput[4]);
        Helper::assertLogstash(['ERROR', 'Error (error)'], $stdOutput[5]);
        Helper::assertLogstash(['CRITICAL', 'Error (exception)'], $stdOutput[6]);
        Helper::assertLogstash(['WARNING', 'Legacy'], $stdOutput[7]);
        Helper::assertLogstash(['WARNING', '   Message'], $stdOutput[8]);
        Helper::assertLogstash(['CRITICAL', 'Command Exception: Some message'], $stdOutput[9]);
    }

    public function testMuteErrorsAndNonZeroOnError(): void
    {
        $cmdResult = Helper::executeReal('test:output', [
            'output-mode'       => 'logstash',
            'exception'         => 'Some message',
            'mute-errors'       => null,
            'non-zero-on-error' => null,
        ]);
        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);

        $stdOutput = Helper::prepareLogstash($cmdResult->std);
        isCount(10, $stdOutput);
        Helper::assertLogstash(['NOTICE', 'Normal 1'], $stdOutput[0]);
        Helper::assertLogstash(['NOTICE', 'Normal 2'], $stdOutput[1]);
        Helper::assertLogstash(['ERROR', 'Message'], $stdOutput[2]);
        Helper::assertLogstash(['WARNING', 'Verbose2 -vv'], $stdOutput[3]);
        Helper::assertLogstash(['ERROR', 'Error (e)'], $stdOutput[4]);
        Helper::assertLogstash(['ERROR', 'Error (error)'], $stdOutput[5]);
        Helper::assertLogstash(['CRITICAL', 'Error (exception)'], $stdOutput[6]);
        Helper::assertLogstash(['WARNING', 'Legacy'], $stdOutput[7]);
        Helper::assertLogstash(['WARNING', '   Message'], $stdOutput[8]);
        Helper::assertLogstash(['CRITICAL', 'Command Exception: Some message'], $stdOutput[9]);
    }

    public function testExtraContext(): void
    {
        $cmdResult = Helper::executeReal('test:output', [
            'output-mode'   => 'logstash',
            'extra-context' => null,
        ]);

        isSame(0, $cmdResult->code);
        isSame('', $cmdResult->err);

        $stdOutput = Helper::prepareLogstash($cmdResult->std);

        isSame(['bar' => 1], $stdOutput[0]->find('context.foo'));
        isSame('line #1', $stdOutput[0]->find('context.zzz'));
        Helper::assertLogstash(['NOTICE', 'Message with extra context #1'], $stdOutput[0]);

        isSame(['bar' => 2], $stdOutput[1]->find('context.foo'));
        isSame('line #2', $stdOutput[1]->find('context.zzz'));
        Helper::assertLogstash(['NOTICE', 'Message with extra context #2'], $stdOutput[1]);

        isSame(['bar' => 2, 'zzz' => 3], $stdOutput[2]->find('context.foo'));
        isSame('line #3', $stdOutput[2]->find('context.zzz'));
        Helper::assertLogstash(['NOTICE', 'Message with extra context #3'], $stdOutput[2]);
    }

    public function testCronAlias(): void
    {
        $cmdResult = Helper::executeReal('test:output', [
            'output-mode' => 'logstash',
            'exception'   => 'Some message',
            'cron'        => null,
        ]);

        isSame(1, $cmdResult->code);
        isSame('', $cmdResult->err);

        // `--cron` has higher proirity than `--output-mode=logstash`
        isContain('] Normal 1', $cmdResult->std);
    }

    public function testTraceId(): void
    {
        $cmdResult = Helper::executeReal(
            'test:output',
            ['output-mode' => 'logstash', 'exception' => 'Some message', '-vvv' => null],
        );
        isSame(1, $cmdResult->code);
        isSame('', $cmdResult->err);

        $stdOutput = Helper::prepareLogstash($cmdResult->std);
        isCount(20, $stdOutput);

        // Trace id is UUID v4
        $pattern = '/[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}/';
        isSame(1, \preg_match($pattern, $stdOutput[0]->find('context.trace.id')));

        $traces = [];

        foreach ($stdOutput as $log) {
            $traces[] = $log->find('context.trace.id');
        }

        isCount(1, \array_unique($traces));

        $cmdResult2 = Helper::executeReal('test:output', ['output-mode' => 'logstash']);
        $stdOutput2 = Helper::prepareLogstash($cmdResult2->std);
        isNotSame($stdOutput[0]->find('context.trace.id'), $stdOutput2[0]->find('context.trace.id'));
    }

    /**
     * Recursively replaces the values in the given array with their corresponding data types.
     */
    private static function replaceValues(array &$array): array
    {
        foreach ($array as &$value) {
            if (\is_array($value)) {
                self::replaceValues($value);
            } else {
                $value = \gettype($value);
            }
        }

        return $array;
    }
}
