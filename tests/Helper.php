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

use JBZoo\Cli\CliApplication;
use JBZoo\TestApp\Commands\TestCliOptions;
use JBZoo\TestApp\Commands\TestCliStdIn;
use JBZoo\TestApp\Commands\TestSleep;
use JBZoo\Utils\Cli;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class Helper
 * @package JBZoo\PHPUnit
 */
class Helper extends PHPUnit
{
    /**
     * @param string $command
     * @param array  $args
     * @param string $prefix
     * @return string
     */
    public static function executeReal(
        string $command,
        array $args = [],
        string $prefix = ''
    ): string {
        $rootPath = __DIR__ . '/fake-app';
        $args['no-ansi'] = null;

        return Cli::exec("{$prefix} php {$rootPath}/cli-wrapper.php {$command}", $args, $rootPath);
    }

    /**
     * @param string $command
     * @param array  $args
     * @return string
     */
    public static function executeVirtaul(string $command, array $args = []): string
    {
        $application = new CliApplication();
        $application->add(new TestCliOptions());
        $application->add(new TestCliStdIn());
        $application->add(new TestSleep());
        $command = $application->find($command);

        $buffer = new BufferedOutput();
        $args = new StringInput(Cli::build('', $args));
        $exitCode = $command->run($args, $buffer);

        if ($exitCode) {
            throw new Exception($buffer->fetch());
        }

        return $buffer->fetch();
    }
}
