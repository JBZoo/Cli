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
use JBZoo\Cli\Cli;

/**
 * Class ExamplesStyles
 */
class ExamplesStyles extends CliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('examples:styles')
            ->setDescription('Examples of new CLI colors and styles');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeAction(): int
    {
        // Render list of values
        $this->_('Render list of values');
        $this->_(Cli::renderList([
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
        $this->_(Cli::renderList($listOfExamples, '*'));
        $this->_();

        // Default success exist code is "0". Max value is 255.
        // See JBZoo\Cli\Codes class for more info
        return Codes::OK;
    }
}
