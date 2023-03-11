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

namespace JBZoo\PHPUnit\TestApp\Commands;

use JBZoo\Cli\CliCommand;
use JBZoo\Cli\Exception;

/**
 * Class TestException
 */
class TestException extends CliCommand
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('test:exception');
        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeAction(): int
    {
        throw new Exception('Error message');
    }
}
