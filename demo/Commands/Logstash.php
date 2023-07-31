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

namespace DemoApp\Commands;

use JBZoo\Cli\CliCommand;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Logstash extends CliCommand
{
    protected function configure(): void
    {
        $this->setName('logstash');
        parent::configure();
    }

    protected function executeAction(): int
    {
        // create a log channel
        $log     = new Logger('ep-portal');
        $handler = new StreamHandler('php://stdout');

        $log->pushHandler($handler->setFormatter(new LogstashFormatter('App name')));

        $log->info('message1');
        $log->notice('message2');
        $log->notice('message3');

        return self::SUCCESS;
    }
}
