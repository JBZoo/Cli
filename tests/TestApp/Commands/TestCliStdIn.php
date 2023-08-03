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
use Symfony\Component\Console\Input\InputOption;

class TestCliStdIn extends CliCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('test:cli-stdin')
            ->addOption('var-dump', null, InputOption::VALUE_NONE);
        parent::configure();
    }

    protected function executeAction(): int
    {
        if ($this->getOptBool('var-dump')) {
            \ob_start();
            \var_dump(self::getStdIn());
            $this->_(\ob_get_clean());
        } else {
            $this->_(self::getStdIn());
        }

        return 0;
    }
}
