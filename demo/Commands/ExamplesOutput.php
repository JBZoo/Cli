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
use JBZoo\Cli\Helper;
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
            ->addOption('exception', 'e', InputOption::VALUE_NONE, 'Throw exception');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeAction(): int
    {
        // Any message that is output in the classic way (echo, print, print_r, ect)
        // Will be caught and output at the end of the script run.
        echo 'Legacy method. Message #1';


        // ./my-app examples:output
        $this->_('Regular message');
        $this->_([
            ' * Several',
            ' * lines',
            ' * message'
        ]);
        $this->_(); // Break the line


        // Render list of values
        $this->_('Render list of values');
        $this->_(Helper::renderList([
            ' Key'    => 'Value',
            ' Key #2' => 123
        ], '-'));


        /**
         * Literally you can use the tags:
         *  - <color>Text</color>
         *  - <color-bold>Text</color-bold>
         *  - <color-under>Text</color-under>
         *  - <color-blink>Text</color-blink>
         *  - <color-bg>Text</color-bg>
         */
        $this->_('Use different styles/colors to make terminal reading life easier');
        $availableColors = ['black', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white', 'default'];
        $listOfExamples = [];
        foreach ($availableColors as $color) {
            $listOfExamples[$color] = implode(' ', [
                "<{$color}>Regular</{$color}>",
                "<{$color}-bold>Bold</{$color}-bold>",
                "<{$color}-under>Underlined</{$color}-under>",
                "<{$color}-bg>Background</{$color}-bg>",
                "<{$color}-blink>Blink</{$color}-blink>",
            ]);
        }
        $this->_(Helper::renderList($listOfExamples, '*'));
        $this->_();

        // Info output
        // ./my-app examples:output -v
        $this->_('Verbose message #1 (-v)', Helper::VERB_V);                                  // No label
        $this->_('Verbose message #2 (-v)', Helper::VERB_INFO);                               // With Label
        $this->_(['Verbose message #3 (-v)', 'Verbose message #3 (-v)'], Helper::VERB_INFO);  // With Label, multi lines
        if ($this->isInfoLevel()) {
            $this->_();
        }


        // Warning output
        // ./my-app examples:output -vv
        $this->_('Very verbose or not critical warning messages #1 (-vv)', Helper::VERB_VV);      // No label
        $this->_('Very verbose or not critical warning messages #2 (-vv)', Helper::VERB_WARNING); // With Label
        if ($this->isWarningLevel()) {
            $this->_();
        }


        // Debug output
        // ./my-app examples:output -vvv
        $this->_('Super low-level message for developers #1 (-vvv)', Helper::VERB_VVV);     // No label
        $this->_('Super low-level message for developers #2 (-vvv)', Helper::VERB_DEBUG);   // With Label
        if ($this->isDebugLevel()) {
            $this->_();
        }


        // If output is hidden, we can use this method to show the message. It's like "always"
        // ./my-app examples:output -q
        $this->_('Show always (-q)', Helper::VERB_QUIET);
        $this->_();


        // Error output (StdErr)
        // ./my-app examples:output -vvv > /dev/null
        $this->_('Your error message in runtime (non-stop)', Helper::VERB_ERROR);
        $this->_('Your exception message in runtime (non-stop)', Helper::VERB_EXCEPTION);
        $this->_();


        // If we want to throw an exception, we can use this way
        // ./my-app examples:output -e                   # Show all messages and shot exceton info
        // ./my-app examples:output -e -vvv              # Show all messages and full exceptio info
        // ./my-app examples:output -e > /dev/null       # Show only error messages (StdErr)
        // ./my-app examples:output -e --mute-errors     # Don't send error code on exceptions (on your own risk!)
        if ($this->getOptBool('exception')) {
            throw new Exception('Exception like managable fatal error');
        }


        // Default success exist code is "0". Max value is 255.
        // See JBZoo\Cli\Codes class for more info
        return Codes::OK;
    }
}
