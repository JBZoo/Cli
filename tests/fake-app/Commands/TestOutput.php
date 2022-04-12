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
use JBZoo\Cli\CliHelper;
use JBZoo\Cli\Exception;
use Symfony\Component\Console\Input\InputOption;

/**
 *
 */
class TestOutput extends CliCommand
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this
            ->setName('test:output')
            ->addOption('exception', null, InputOption::VALUE_OPTIONAL, 'Throw exception')
            ->addOption('type-of-vars', null, InputOption::VALUE_NONE, 'Check type of vars');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeAction(): int
    {
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
        echo " ";
        echo "";
        echo "  Message  ";
        echo "\t";
        
        $this->_(['Normal 1', 'Normal 2']);
        $this->_('Message', CliHelper::VERB_ERROR);

        $this->_('Info1 -v', CliHelper::VERB_V);
        $this->_('Info2 -v', CliHelper::VERB_INFO);

        $this->_('Verbose1 -vv', CliHelper::VERB_VV);
        $this->_('Verbose2 -vv', CliHelper::VERB_WARNING);

        $this->_('Debug1 -vvv', CliHelper::VERB_VVV);
        $this->_([
            'Message #1 -vvv',
            'Message #2 -vvv'
        ], CliHelper::VERB_DEBUG);

        $this->_('Error (e)', CliHelper::VERB_E);
        $this->_('Error (error)', CliHelper::VERB_ERROR);
        $this->_('Error (exception)', CliHelper::VERB_EXCEPTION);

        $this->_('Quiet -q', CliHelper::VERB_QUIET);

        if ($exception = $this->getOptString('exception')) {
            throw new Exception($exception);
        }

        return 0;
    }
}
