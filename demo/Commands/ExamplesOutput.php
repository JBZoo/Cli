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
use JBZoo\Cli\Codes;
use JBZoo\Cli\OutLvl;
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
            ->addOption('throw-custom-exception', 'e', InputOption::VALUE_NONE, 'Throw the exception');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeAction(): int
    {
        $code = function (string $flag): string {
            return "<black-bold>`cli(\$text, {$flag})`</black-bold>";
        };

        // Legacy way
        echo "Any message that is output in the classic way (<comment>echo, print, print_r, ...</comment>).\n";
        echo "The output will be caught and print at the end of the script run with legacy mark.";

        $tag1 = "<black-bold>`";
        $tag2 = "`</black-bold>";

        // If output is hidden, we can use this method to show the message. It's like "always"
        // ./my-app examples:output --quiet
        $this->_("You can see the line even if {$tag1}--quiet{$tag2} is used. {$code('OutLvl::Q')}", OutLvl::Q);
        $this->_();


        // ./my-app examples:output
        $this->_('Regular message');
        //$this->_(['Several', '    lines', '        message.']);
        $this->_(); // Break the line

        // `cli($text)` is global alias for `$this->_();`

        // Info output
        // ./my-app examples:output -v
        cli("Verbose message #1       {$code('OutLvl::V')}    (-v)", OutLvl::V);
        cli("Verbose message #2 {$code('OutLvl::INFO')} (-v)", OutLvl::INFO);
        if ($this->isInfoLevel()) {
            cli();
        }


        // Warning output
        // ./my-app examples:output -vv
        cli("Very verbose or warning message #1          {$code('OutLvl::VV')}      (-vv)", OutLvl::VV);
        cli("Very verbose or warning message #2 {$code('OutLvl::WARNING')} (-vv)", OutLvl::WARNING);
        if ($this->isWarningLevel()) {
            cli();
        }


        // Debug output
        // ./my-app examples:output -vvv
        cli("Low-level message for devs #1        {$code('OutLvl::VVV')}   (-vvv)", OutLvl::VVV);
        cli("Low-level message for devs #2 {$code('OutLvl::DEBUG')} (-vvv)", OutLvl::DEBUG);
        if ($this->isDebugLevel()) {
            cli();
        }


        // Error output (StdErr)
        // ./my-app examples:output -vvv > /dev/null
        cli(
            "Not critical error message in runtime is written to <u>StdErr</u>.        {$code('OutLvl::E')}",
            OutLvl::E
        );
        cli(
            "Not critical error message in runtime is written to <u>StdErr</u>. {$code('OutLvl::ERROR')}",
            OutLvl::ERROR
        );
        cli();


        // If we want to throw an exception, we can use this way
        // ./my-app examples:output -e                   # Show all messages and shot exception info
        // ./my-app examples:output -e -vvv              # Show all messages and full exception info
        // ./my-app examples:output -e > /dev/null       # Show only error messages (StdErr)
        // ./my-app examples:output -e --mute-errors     # Don't send error code on exceptions (on your own risk!)
        if ($this->getOptBool('throw-custom-exception')) {
            throw new Exception("You can ignore exception message via `--mute-errors`. On your own risk!");
        }


        // Default success exist code is "0". Max value is 255.
        return self::SUCCESS;
    }
}
