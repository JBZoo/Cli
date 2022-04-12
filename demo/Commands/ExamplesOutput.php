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

use function JBZoo\Cli\cli;

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
            ->addOption('show-custom-exception', 'e', InputOption::VALUE_NONE, 'Throw the exception');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeAction(): int
    {
        // Legacy way
        echo "Any message that is output in the classic way (<comment>echo, print, print_r, ...</comment>).\n";
        echo "The output will be caught and print at the end of the script run with legacy mark.";


        $t1 = "<black-bold>`";
        $t2 = "`</black-bold>";


        // If output is hidden, we can use this method to show the message. It's like "always"
        // ./my-app examples:output -q
        $this->_("You can see this line even if {$t1}--quiet{$t2} or {$t1}-q{$t2} is used.", CliHelper::QUIET);
        $this->_();


        // ./my-app examples:output
        $this->_('Regular message');
        //$this->_(['Several', '    lines', '        message.']);
        $this->_(); // Break the line

        // `cli($text)` is global alias for `$this->_();`

        // Info output
        // ./my-app examples:output -v
        cli("Verbose message #1       {$t1}CliHelper::V{$t2}    (-v)", CliHelper::V);
        cli("Verbose message #2 {$t1}CliHelper::INFO{$t2} (-v)", CliHelper::INFO);
        $this->isInfoLevel() && cli();


        // Warning output
        // ./my-app examples:output -vv
        cli("Very verbose or warning message #1         {$t1}CliHelper::VV{$t2}       (-vv)", CliHelper::VV);
        cli("Very verbose or warning message #2 {$t1}CliHelper::WARNING{$t2} (-vv)", CliHelper::WARNING);
        $this->isWarningLevel() && cli();


        // Debug output
        // ./my-app examples:output -vvv
        cli("Low-level message for devs #1        {$t1}CliHelper::VVV{$t2}   (-vvv)", CliHelper::VVV);
        cli("Low-level message for devs #2 {$t1}CliHelper::DEBUG{$t2} (-vvv)", CliHelper::DEBUG);
        $this->isDebugLevel() && cli();


        // Error output (StdErr)
        // ./my-app examples:output -vvv > /dev/null
        cli("Not critical error message in runtime to <u>StdErr</u>.        {$t1}CliHelper::E{$t2}", CliHelper::E);
        cli("Not critical error message in runtime to <u>StdErr</u>. {$t1}CliHelper::ERROR{$t2}", CliHelper::ERROR);
        cli();


        // If we want to throw an exception, we can use this way
        // ./my-app examples:output -e                   # Show all messages and shot exception info
        // ./my-app examples:output -e -vvv              # Show all messages and full exception info
        // ./my-app examples:output -e > /dev/null       # Show only error messages (StdErr)
        // ./my-app examples:output -e --mute-errors     # Don't send error code on exceptions (on your own risk!)
        if ($this->getOptBool('show-custom-exception')) {
            throw new Exception("You can ignore exception message via `--mute-errors`. On your own risk!");
        }


        // Default success exist code is "0". Max value is 255.
        // See JBZoo\Cli\Codes class for more info
        return Codes::OK;
    }
}
