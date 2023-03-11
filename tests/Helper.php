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
use JBZoo\Utils\Sys;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;

/**
 * Class Helper
 * @package JBZoo\PHPUnit
 */
class Helper extends PHPUnit
{
    /**
     * @param string $command
     * @param array  $options
     * @param string $preAction
     * @param string $postAction
     * @return array
     */
    public static function executeReal(
        string $command,
        array $options = [],
        string $preAction = '',
        string $postAction = ''
    ): array {
        $cwd = __DIR__ . '/TestApp';
        $options['no-ansi'] = null;

        $realCommand = trim(implode(' ', [
            $preAction,
            Sys::getBinary(),
            Cli::build("{$cwd}/cli-wrapper.php {$command}", $options),
            '',
            $postAction
        ]));

        //dump($realCommand);
        $process = Process::fromShellCommandline($realCommand, $cwd, null, null, 3600);
        $process->run();

        return [$process->getExitCode(), trim($process->getOutput()), trim($process->getErrorOutput())];
    }

    public static function executeVirtaul(string $command, array $options = []): string
    {
        $rootPath = dirname(__DIR__);

        putenv("JBZOO_PATH_BIN={$rootPath}/tests/TestApp/cli-wrapper.php");
        putenv("JBZOO_PATH_ROOT={$rootPath}/tests/TestApp");

        $application = new CliApplication();
        $application->add(new TestCliOptions());
        $application->add(new TestCliStdIn());
        $application->add(new TestSleep());
        $application->add(new TestSleepMulti());
        $application->add(new TestProgress());
        $command = $application->find($command);

        $buffer = new BufferedOutput();
        $inputString = new StringInput(Cli::build('', $options));
        $exitCode = $command->run($inputString, $buffer);

        if ($exitCode) {
            throw new Exception($buffer->fetch());
        }

        return $buffer->fetch();
    }
}
