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
use JBZoo\Cli\Exception;
use JBZoo\Cli\Helper;
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
            ->addOption('exception', null, InputOption::VALUE_OPTIONAL, 'Throw exception', '');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeAction(): int
    {
        $this->_(['Normal 1', 'Normal 2']);
        $this->_('Message', Helper::VERB_ERROR);

        $this->_('Info1 -v', Helper::VERB_V);
        $this->_('Info2 -v', Helper::VERB_INFO);

        $this->_('Verbose1 -vv', Helper::VERB_VV);
        $this->_('Verbose2 -vv', Helper::VERB_WARNING);

        $this->_('Debug1 -vvv', Helper::VERB_VVV);
        $this->_([
            'Message #1 -vvv',
            'Message #2 -vvv'
        ], Helper::VERB_DEBUG);

        $this->_('Quiet -q', Helper::VERB_QUIET);

        if ($exception = $this->getOptString('exception')) {
            throw new Exception($exception);
        }

        return 0;
    }
}
