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

namespace JBZoo\TestApp\Commands;

use JBZoo\Cli\CliCommand;

/**
 *
 */
class TestOutput extends CliCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('test:output');
        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeAction(): int
    {
        $this->_('Normal 1');
        $this->_('Normal 2', '');
        $this->_('Message', 'error');

        $this->_('Info1 -v', 'v');
        $this->_('Info2 -v', 'info');

        $this->_('Verbose1 -vv', 'vv');
        $this->_('Verbose2 -vv', 'warn');

        $this->_('Debug1 -vvv', 'vvv');
        $this->_('Debug2 -vvv', 'debug');

        $this->_('Quite -q', 'q');

        return 0;
    }
}
