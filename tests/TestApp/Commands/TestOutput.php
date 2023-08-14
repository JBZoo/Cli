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
use JBZoo\Cli\CliHelper;
use JBZoo\Cli\Exception;
use JBZoo\Cli\OutLvl;
use Symfony\Component\Console\Input\InputOption;

use function JBZoo\Cli\cli;

class TestOutput extends CliCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('test:output')
            ->addOption('exception', null, InputOption::VALUE_OPTIONAL, 'Throw exception')
            ->addOption('type-of-vars', null, InputOption::VALUE_NONE, 'Check type of vars')
            ->addOption('extra-context', null, InputOption::VALUE_NONE, 'Check type of vars');

        parent::configure();
    }

    protected function executeAction(): int
    {
        if ($this->getOptBool('extra-context')) {
            CliHelper::getInstance()->appendExtraContext(['foo' => ['bar' => 1]]);
            $this->_('Message with extra context #1', OutLvl::DEFAULT, ['zzz' => 'line #1']);

            CliHelper::getInstance()->appendExtraContext(['foo' => ['bar' => 2]]);
            $this->_('Message with extra context #2', OutLvl::DEFAULT, ['zzz' => 'line #2']);

            CliHelper::getInstance()->appendExtraContext(['foo' => ['zzz' => 3]]);
            $this->_('Message with extra context #3', OutLvl::DEFAULT, ['zzz' => 'line #3']);

            return 0;
        }

        if ($this->getOptBool('type-of-vars')) {
            $this->_(' ');
            $this->_(0);
            $this->_(true);
            $this->_(false);
            $this->_(null);
            $this->_(1.0);
            $this->_(1);
            $this->_(-0.001);

            return 0;
        }

        echo "\n";
        echo "Legacy    \n";
        echo ' ';
        echo '';
        echo '  Message  ';
        echo "\t";

        $this->_(['Normal 1', 'Normal 2']);
        $this->_('Message', OutLvl::ERROR);

        cli('Info1 -v', OutLvl::V);
        cli('Info2 -v', OutLvl::INFO);

        $this->_('Verbose1 -vv', OutLvl::VV);
        $this->_('Verbose2 -vv', OutLvl::WARNING);

        $this->_('Debug1 -vvv', OutLvl::VVV);
        $this->_([
            'Message #1 -vvv',
            'Message #2 -vvv',
        ], OutLvl::DEBUG);

        $this->_('Error (e)', OutLvl::E);
        $this->_('Error (error)', OutLvl::ERROR);
        $this->_('Error (exception)', OutLvl::EXCEPTION);

        $this->_('Message with context', OutLvl::DEBUG, ['foo' => 'bar']);

        $this->_('Quiet -q', OutLvl::Q);

        if ($exception = $this->getOptString('exception')) {
            throw new Exception($exception);
        }

        return 0;
    }
}
