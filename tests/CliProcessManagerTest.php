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

use JBZoo\Cli\ProcessManager\ProcessManager;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * The PHPUnit test of the ProcessManager class.
 * @author BluePsyduck <bluepsyduck@gmx.com>
 */
class CliProcessManagerTest extends PHPUnit
{
    /**
     * Tests the constructing.
     */
    public function testConstruct(): void
    {
        $numberOfParallelProcesses = 42;
        $pollInterval              = 1337;
        $processStartDelay         = 21;

        $manager = new ProcessManager($numberOfParallelProcesses, $pollInterval, $processStartDelay);
        self::assertSame($numberOfParallelProcesses, $this->extractProperty($manager, 'numberOfParallelProcesses'));
        self::assertSame($pollInterval, $this->extractProperty($manager, 'pollInterval'));
        self::assertSame($processStartDelay, $this->extractProperty($manager, 'processStartDelay'));
    }

    /**
     * Tests the setNumberOfParallelProcesses method.
     */
    public function testSetNumberOfParallelProcesses(): void
    {
        $numberOfParallelProcesses = 42;

        $manager = $this->getMockBuilder(ProcessManager::class)
            ->onlyMethods(['executeNextPendingProcess'])
            ->setConstructorArgs([])
            ->getMock();
        $manager->expects(self::once())
            ->method('executeNextPendingProcess');

        $result = $manager->setNumberOfParallelProcesses($numberOfParallelProcesses);
        self::assertSame($manager, $result);
        self::assertSame($numberOfParallelProcesses, $this->extractProperty($manager, 'numberOfParallelProcesses'));
    }

    /**
     * Tests the setPollInterval method.
     */
    public function testSetPollInterval(): void
    {
        $pollInterval = 1337;

        $manager = new ProcessManager();

        $result = $manager->setPollInterval($pollInterval);
        self::assertSame($manager, $result);
        self::assertSame($pollInterval, $this->extractProperty($manager, 'pollInterval'));
    }

    /**
     * Tests the setProcessStartDelay method.
     */
    public function testSetProcessStartDelay(): void
    {
        $processStartDelay = 21;

        $manager = new ProcessManager();

        $result = $manager->setProcessStartDelay($processStartDelay);
        self::assertSame($manager, $result);
        self::assertSame($processStartDelay, $this->extractProperty($manager, 'processStartDelay'));
    }

    /**
     * Tests the setProcessStartCallback method.
     */
    public function testSetProcessStartCallback(): void
    {
        $callback = 'strval';

        $manager = new ProcessManager();
        $result  = $manager->setProcessStartCallback($callback);
        self::assertSame($manager, $result);
        self::assertSame($callback, $this->extractProperty($manager, 'processStartCallback'));
    }

    /**
     * Tests the setProcessFinishCallback method.
     */
    public function testSetProcessFinishCallback(): void
    {
        $callback = 'strval';

        $manager = new ProcessManager();
        $result  = $manager->setProcessFinishCallback($callback);
        self::assertSame($manager, $result);
        self::assertSame($callback, $this->extractProperty($manager, 'processFinishCallback'));
    }

    /**
     * Tests the setProcessTimeoutCallback method.
     */
    public function testSetProcessTimeoutCallback(): void
    {
        $callback = 'strval';

        $manager = new ProcessManager();
        $result  = $manager->setProcessTimeoutCallback($callback);
        self::assertSame($manager, $result);
        self::assertSame($callback, $this->extractProperty($manager, 'processTimeoutCallback'));
    }

    /**
     * Tests the setProcessCheckCallback method.
     */
    public function testSetProcessCheckCallback(): void
    {
        $callback = 'strval';

        $manager = new ProcessManager();
        $result  = $manager->setProcessCheckCallback($callback);
        self::assertSame($manager, $result);
        self::assertSame($callback, $this->extractProperty($manager, 'processCheckCallback'));
    }

    /**
     * Tests the invokeCallback method.
     */
    #[DataProvider('provideInvokeCallbackCases')]
    public function testInvokeCallback(bool $withCallback): void
    {
        $process = $this->createMock(Process::class);

        $callback        = null;
        $missingCallback = false;
        if ($withCallback) {
            $missingCallback = true;
            $callback        = function (Process $p) use ($process, &$missingCallback): void {
                $this->assertSame($process, $p);
                $missingCallback = false;
            };
        }

        $manager = new ProcessManager();
        $this->invokeMethod($manager, 'invokeCallback', $callback, $process);
        self::assertFalse($missingCallback);
    }

    /**
     * Provides the data for the invokeCallback test.
     * @return array[]
     */
    public static function provideInvokeCallbackCases(): iterable
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * Tests the addProcess method.
     */
    public function testAddProcess(): void
    {
        $process  = $this->createMock(Process::class);
        $callback = 'strval';
        $env      = ['abc' => 'def'];

        $pendingProcessData = [
            ['foo', 'bar'],
        ];
        $expectedPendingProcessData = [
            ['foo', 'bar'],
            [$process, $callback, $env],
        ];

        $manager = $this->getMockBuilder(ProcessManager::class)
            ->onlyMethods(['executeNextPendingProcess', 'checkRunningProcesses'])
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects(self::once())
            ->method('executeNextPendingProcess');

        $manager->expects(self::once())
            ->method('checkRunningProcesses');

        $this->injectProperty($manager, 'pendingProcessData', $pendingProcessData);

        $result = $manager->addProcess($process, $callback, $env);
        self::assertSame($manager, $result);
        self::assertEquals($expectedPendingProcessData, $this->extractProperty($manager, 'pendingProcessData'));
    }

    /**
     * Tests the executeNextPendingProcess method.
     */
    #[DataProvider('provideExecuteNextPendingProcessCases')]
    public function testExecuteNextPendingProcess(?int $pid, bool $expectRunningProcess, bool $expectCheck): void
    {
        $processStartDelay    = 1337;
        $callback             = 'strval';
        $processStartCallback = 'intval';
        $env                  = ['foo' => 'bar'];

        $process = $this->createMock(Process::class);
        $process->expects(self::once())
            ->method('start')
            ->with($callback, $env);
        $process->expects(self::once())
            ->method('getPid')
            ->willReturn($pid);

        $pendingProcessData = [
            [$process, $callback, $env],
            ['abc', 'def'],
        ];
        $expectedPendingProcessData = [
            ['abc', 'def'],
        ];

        $runningProcesses         = [1337 => 'ghi'];
        $expectedRunningProcesses = $expectRunningProcess ? [1337 => 'ghi', 42 => $process] : ['1337' => 'ghi'];

        $manager = $this->getMockBuilder(ProcessManager::class)
            ->onlyMethods([
                'canExecuteNextPendingRequest',
                'sleep',
                'invokeCallback',
                'checkRunningProcess',
            ])
            ->setConstructorArgs([0, 0, $processStartDelay])
            ->getMock();
        $manager->expects(self::once())
            ->method('canExecuteNextPendingRequest')
            ->willReturn(true);
        $manager->expects(self::once())
            ->method('sleep')
            ->with($processStartDelay);
        $manager->expects($expectCheck ? self::once() : self::never())
            ->method('checkRunningProcess')
            ->with($pid, $process);

        $manager->expects(self::once())
            ->method('invokeCallback')
            ->with($processStartCallback, $process);

        $this->injectProperty($manager, 'pendingProcessData', $pendingProcessData);
        $this->injectProperty($manager, 'runningProcesses', $runningProcesses);
        $this->injectProperty($manager, 'processStartCallback', $processStartCallback);

        $this->invokeMethod($manager, 'executeNextPendingProcess');

        self::assertEquals($expectedPendingProcessData, $this->extractProperty($manager, 'pendingProcessData'));
        self::assertEquals($expectedRunningProcesses, $this->extractProperty($manager, 'runningProcesses'));
    }

    /**
     * Provides the data for the executeNextPendingProcess test.
     * @return array[]
     */
    public static function provideExecuteNextPendingProcessCases(): iterable
    {
        return [
            [42, true, false],
            [null, false, true],
        ];
    }

    /**
     * Tests the canExecuteNextPendingRequest method.
     * @param array<Process<string>> $runningProcesses
     * @param array[]                $pendingProcessData
     */
    #[DataProvider('provideCanExecuteNextPendingRequestCases')]
    public function testCanExecuteNextPendingRequest(
        int $numberOfParallelProcesses,
        array $runningProcesses,
        array $pendingProcessData,
        bool $expectedResult,
    ): void {
        $manager = new ProcessManager($numberOfParallelProcesses);
        $this->injectProperty($manager, 'pendingProcessData', $pendingProcessData);
        $this->injectProperty($manager, 'runningProcesses', $runningProcesses);

        $result = $this->invokeMethod($manager, 'canExecuteNextPendingRequest');
        self::assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the canExecuteNextPendingRequest test.
     * @return array[]
     */
    public static function provideCanExecuteNextPendingRequestCases(): iterable
    {
        return [
            [4, ['abc'], ['foo'], true],
            [4, ['abc', 'def', 'ghi', 'jkl'], ['foo'], false],
            [4, ['abc'], [], false],
        ];
    }

    /**
     * Tests the checkRunningProcesses method.
     * @throws \ReflectionException
     */
    public function testCheckRunningProcesses(): void
    {
        $process1 = $this->createMock(Process::class);
        $process2 = $this->createMock(Process::class);

        $manager = $this->getMockBuilder(ProcessManager::class)
            ->onlyMethods(['checkRunningProcess'])
            ->disableOriginalConstructor()
            ->getMock();

        $manager
            ->expects(self::exactly(2))
            ->method('checkRunningProcess')
            ->with(self::callback(static function ($arg) use (&$process1, &$process2) {
                static $callCount = 0;
                $expected         = [
                    [42, $process1],
                    [1337, $process2],
                ];

                return $arg === $expected[$callCount++][0];
            }));

        $this->injectProperty($manager, 'runningProcesses', [42 => $process1, 1337 => $process2]);

        $this->invokeMethod($manager, 'checkRunningProcesses');
    }

    /**
     * Tests the checkRunningProcess method.
     */
    #[DataProvider('provideCheckRunningProcessCases')]
    public function testCheckRunningProcess(
        ?int $pid,
        bool $resultIsRunning,
        bool $expectFinish,
        bool $expectUnset,
    ): void {
        $process = $this->createMock(Process::class);
        $process->expects(self::any())
            ->method('isRunning')
            ->willReturn($resultIsRunning);

        $process2                 = $this->createMock(Process::class);
        $runningProcesses         = [42 => $process, 1337 => $process2];
        $expectedRunningProcesses = $expectUnset ? [1337 => $process2] : [42 => $process, 1337 => $process2];

        $manager = $this->getMockBuilder(ProcessManager::class)
            ->onlyMethods(['checkProcessTimeout', 'invokeCallback', 'executeNextPendingProcess'])
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects(self::once())
            ->method('checkProcessTimeout')
            ->with($process);

        $manager->expects(self::exactly($expectFinish ? 2 : 1))
            ->method('invokeCallback')
            ->with(self::callback(static function ($arg) use (&$process) {
                static $callCount = 0;
                $expected         = [
                    ['intval', $process],
                    ['strval', $process],
                ];

                return $arg === $expected[$callCount++][0];
            }));

        $manager->expects($expectFinish ? self::once() : self::never())
            ->method('executeNextPendingProcess');

        $manager->setProcessFinishCallback('strval')
            ->setProcessCheckCallback('intval');

        $this->injectProperty($manager, 'runningProcesses', $runningProcesses);

        $this->invokeMethod($manager, 'checkRunningProcess', $pid, $process);
        self::assertEquals($expectedRunningProcesses, $this->extractProperty($manager, 'runningProcesses'));
    }

    /**
     * Provides the data for the checkRunningProcess test.
     * @return array[]
     */
    public static function provideCheckRunningProcessCases(): iterable
    {
        return [
            [42, true, false, false],
            [42, false, true, true],
            [42, false, true, true],
            [null, false, true, false],
        ];
    }

    /**
     * Tests the checkProcessTimeout method.
     */
    #[DataProvider('provideCheckProcessTimeoutCases')]
    public function testCheckProcessTimeout(bool $throwException, bool $expectInvoke): void
    {
        $process = $this->createMock(Process::class);

        if ($throwException) {
            $process->expects(self::once())
                ->method('checkTimeout')
                ->willThrowException($this->createMock(ProcessTimedOutException::class));
        } else {
            $process->expects(self::once())
                ->method('checkTimeout');
        }

        $manager = $this->getMockBuilder(ProcessManager::class)
            ->onlyMethods(['invokeCallback'])
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects($expectInvoke ? self::once() : self::never())
            ->method('invokeCallback')
            ->with('strval', $process);
        $manager->setProcessTimeoutCallback('strval');

        $this->invokeMethod($manager, 'checkProcessTimeout', $process);
    }

    /**
     * Provides the data for the checkProcessTimeout test.
     * @return array[]
     */
    public static function provideCheckProcessTimeoutCases(): iterable
    {
        return [
            [false, false],
            [true, true],
        ];
    }

    /**
     * Tests the waitForAllProcesses method.
     */
    public function testWaitForAllProcesses(): void
    {
        $pollInterval = 1337;

        $manager = $this->getMockBuilder(ProcessManager::class)
            ->onlyMethods(['hasUnfinishedProcesses', 'sleep', 'checkRunningProcesses'])
            ->setConstructorArgs([42, $pollInterval, 21])
            ->getMock();
        $manager->expects(self::exactly(3))
            ->method('hasUnfinishedProcesses')
            ->willReturnOnConsecutiveCalls(
                true,
                true,
                false,
            );
        $manager->expects(self::exactly(2))
            ->method('sleep')
            ->with($pollInterval);
        $manager->expects(self::exactly(2))
            ->method('checkRunningProcesses');

        $result = $manager->waitForAllProcesses();
        self::assertSame($manager, $result);
    }

    /**
     * Tests the sleep method.
     */
    public function testSleep(): void
    {
        $milliseconds = 100;
        $manager      = new ProcessManager();

        $startTime = \microtime(true);
        $this->invokeMethod($manager, 'sleep', $milliseconds);
        $endTime = \microtime(true);
        self::assertTrue($endTime >= $startTime + $milliseconds / 1000);
    }

    /**
     * Tests the hasUnfinishedProcesses method.
     * @param array[]                $pendingProcessData
     * @param array<Process<string>> $runningProcesses
     */
    #[DataProvider('provideHasUnfinishedProcessesCases')]
    public function testHasUnfinishedProcesses(
        array $pendingProcessData,
        array $runningProcesses,
        bool $expectedResult,
    ): void {
        $manager = new ProcessManager();
        $this->injectProperty($manager, 'pendingProcessData', $pendingProcessData);
        $this->injectProperty($manager, 'runningProcesses', $runningProcesses);

        $result = $manager->hasUnfinishedProcesses();
        self::assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the hasUnfinishedProcesses test.
     * @return array[]
     */
    public static function provideHasUnfinishedProcessesCases(): iterable
    {
        return [
            [[['abc' => 'def']], [], true],
            // [[], [$this->createMock(Process::class)], true],
            [[], [], false],
        ];
    }

    /**
     * Injects a property value into an object.
     * @param mixed $value
     */
    protected function injectProperty(object $object, string $name, $value): self
    {
        $reflectedProperty = new \ReflectionProperty($object, $name);
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($object, $value);

        return $this;
    }

    /**
     * Injects a static property value into a class.
     * @param mixed $value
     */
    protected function injectStaticProperty(string $className, string $name, $value): self
    {
        $reflectedClass = new \ReflectionClass($className);
        $reflectedClass->setStaticPropertyValue($name, $value);

        return $this;
    }

    /**
     * Extracts a property value from an object.
     */
    protected function extractProperty(object $object, string $name): mixed
    {
        $reflectedProperty = new \ReflectionProperty($object, $name);
        $reflectedProperty->setAccessible(true);

        return $reflectedProperty->getValue($object);
    }

    /**
     * Extracts a static property value from a class.
     */
    protected function extractStaticProperty(string $className, string $name): mixed
    {
        $reflectedClass = new \ReflectionClass($className);

        return $reflectedClass->getStaticPropertyValue($name);
    }

    /**
     * Invokes a method on an object.
     * @param mixed ...$params
     */
    protected function invokeMethod(object $object, string $name, ...$params): mixed
    {
        $reflectedMethod = new \ReflectionMethod($object, $name);
        $reflectedMethod->setAccessible(true);

        return $reflectedMethod->invokeArgs($object, $params);
    }

    /**
     * Invokes a static method on a class.
     * @param mixed ...$params
     */
    protected function invokeStaticMethod(string $className, string $name, ...$params): mixed
    {
        $reflectedClass  = new \ReflectionClass($className);
        $reflectedMethod = $reflectedClass->getMethod($name);
        $reflectedMethod->setAccessible(true);

        return $reflectedMethod->invokeArgs(null, $params);
    }
}
