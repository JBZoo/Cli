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

namespace DemoApp\Commands;

use JBZoo\Cli\CliCommand;
use JBZoo\Cli\CliHelper;
use JBZoo\Cli\Codes;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ExamplesOutput
 */
class ExamplesOutput extends CliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('examples:output')
            ->setDescription('Examples of output and error reporting')
            ->addOption('exception', 'e', InputOption::VALUE_NONE, 'Throw the exception');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeAction(): int
    {
        // Legacy way
        echo "Any message that is output in the classic way (<comment>echo, print, print_r, ect</comment>).\n";
        echo "Will be caught and output at the end of the script run.\n";


        // ./my-app examples:output
        $this->_('Regular message');
        $this->_([
            'Several',
            '    lines',
            '        message.'
        ]);
        $this->_(); // Break the line


        // Info output
        // ./my-app examples:output -v
        $this->_('Verbose message #1 <info>`CliHelper::VERB_V`</info> (-v)', CliHelper::VERB_V);          // No label
        $this->_('Verbose message #2 <info>`CliHelper::VERB_INFO`</info> (-v)', CliHelper::VERB_INFO);    // With Label
        $this->isInfoLevel() && $this->_();


        // Warning output
        // ./my-app examples:output -vv
        $this->_('Very verbose or warning message #1 <info>`CliHelper::VERB_VV`</info> (-vv)', CliHelper::VERB_VV);
        $this->_('Very verbose or warning message #2 <info>`CliHelper::VERB_WARNING`</info> (-vv)', CliHelper::VERB_WARNING);
        $this->isWarningLevel() && $this->_();


        // Debug output
        // ./my-app examples:output -vvv
        $this->_('Low-level message for devs #1 <info>`CliHelper::VERB_VVV`</info> (-vvv)', CliHelper::VERB_VVV);
        $this->_('Low-level message for devs #2 <info>`CliHelper::VERB_DEBUG`</info>  (-vvv)', CliHelper::VERB_DEBUG);
        $this->isDebugLevel() && $this->_();


        // If output is hidden, we can use this method to show the message. It's like "always"
        // ./my-app examples:output -q
        $this->_(
            'You can see this line even if <info>`--quie`</info> or <info>`-q`</info> is used.',
            CliHelper::VERB_QUIET
        );
        $this->_();


        // Error output (StdErr)
        // ./my-app examples:output -vvv > /dev/null
        $this->_(
            'Not critical error message in runtime to StdErr. <info>`CliHelper::VERB_ERROR`</info>',
            CliHelper::VERB_ERROR
        );
        $this->_();


        // If we want to throw an exception, we can use this way
        // ./my-app examples:output -e                   # Show all messages and shot exceton info
        // ./my-app examples:output -e -vvv              # Show all messages and full exceptio info
        // ./my-app examples:output -e > /dev/null       # Show only error messages (StdErr)
        // ./my-app examples:output -e --mute-errors     # Don't send error code on exceptions (on your own risk!)
        if ($this->getOptBool('exception')) {
            throw new Exception('You can iggnored exception message <info>`--mute-errors`</info>. On your own risk!');
        }


        // Default success exist code is "0". Max value is 255.
        // See JBZoo\Cli\Codes class for more info
        return Codes::OK;
    }
}
