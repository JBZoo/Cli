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
use JBZoo\PHPUnit\TestApp\Commands\TestCliOptions;
use JBZoo\PHPUnit\TestApp\Commands\TestCliStdIn;
use JBZoo\PHPUnit\TestApp\Commands\TestProgress;
use JBZoo\PHPUnit\TestApp\Commands\TestSleep;
use JBZoo\PHPUnit\TestApp\Commands\TestSleepMulti;
use JBZoo\Utils\Cli;
use JBZoo\Utils\Env;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;

class Helper
{
    public static function executeReal(
        string $command,
        array $options = [],
        string $preAction = '',
        string $postAction = '',
    ): CmdResult {
        $cwd = __DIR__ . '/TestApp';

        $options['no-ansi'] = null;

        $realCommand = \trim(
            \implode(' ', [
                $preAction,
                Env::string('PHP_BIN', 'php'),
                Cli::build("{$cwd}/cli-wrapper.php {$command}", $options),
                '',
                $postAction,
            ]),
        );

        $process = Process::fromShellCommandline($realCommand, $cwd, null, null, 3600);
        $process->run();

        return new CmdResult(
            $process->getExitCode(),
            \trim($process->getOutput()),
            \trim($process->getErrorOutput()),
            $realCommand,
        );
    }

    public static function executeVirtaul(string $command, array $options = []): CmdResult
    {
        $rootPath = \dirname(__DIR__);

        \putenv("JBZOO_PATH_BIN={$rootPath}/tests/TestApp/cli-wrapper.php");
        \putenv("JBZOO_PATH_ROOT={$rootPath}/tests/TestApp");

        $application = new CliApplication();
        $application->add(new TestCliOptions());
        $application->add(new TestCliStdIn());
        $application->add(new TestSleep());
        $application->add(new TestSleepMulti());
        $application->add(new TestProgress());
        $command = $application->find($command);

        $buffer      = new BufferedOutput();
        $inputString = new StringInput(Cli::build('', $options));
        $exitCode    = $command->run($inputString, $buffer);

        if ($exitCode) {
            throw new Exception($buffer->fetch());
        }

        return new CmdResult(0, $buffer->fetch(), '', (string)$inputString);
    }

    public static function validateDateFormat(string $logMessage): bool
    {
        // Example: [2023-08-05T17:31:57.421918+04:00]
        $pattern = '/\[\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d+\+\d{2}:\d{2}\]/';
        \preg_match($pattern, $logMessage, $matches);

        return \count($matches) === 1;
    }

    public static function validateProfilerFormat(string $logMessage): bool
    {
        // Example: [+0.057s/  15.44 KB]
        $pattern = '/\[\+\d+\.\d+s\/\s+\d+\.\d+ KB\]/';
        \preg_match($pattern, $logMessage, $matches);

        return \count($matches) === 1;
    }
}
