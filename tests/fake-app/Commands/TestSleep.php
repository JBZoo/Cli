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
use Symfony\Component\Console\Input\InputOption;

/**
 *
 */
class TestSleep extends CliCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('test:sleep')
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, '', 5);

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeAction(): int
    {
        $this->_('Started');
        sleep($this->getOptInt('sleep'));
        $this->_('Finished');

        return 0;
    }
}
