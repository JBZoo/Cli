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

class Simple extends CliCommand
{
    protected function configure(): void
    {
        // Action name. It will be used in command line.
        // Example: `./my-app simple`
        $this->setName('simple');
        parent::configure();
    }

    /**
     * {@inheritDoc}
     */
    protected function executeAction(): int
    {
        // Your code here
        $this->_('Hello world!');

        // Exit code. 0 - success, 1 - error.
        return self::SUCCESS;
    }
}
