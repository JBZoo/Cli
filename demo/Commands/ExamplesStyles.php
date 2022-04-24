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
use JBZoo\Cli\CliRender;
use JBZoo\Cli\Codes;

use function JBZoo\Cli\cli;

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
        cli('Render list of values');
        cli(CliRender::list([
            'Like a title',
            'Key'    => 'Value',
            'Key #2' => 123
        ], '-'));


        /**
         * Literally you can use the tags:
         *  - <color>Text</color>
         *  - <color-b>Text</color-b>
         *  - <color-u>Text</color-ur>
         *  - <color-bl>Text</color-bl>
         *  - <color-bg>Text</color-bg>
         *  - <color-r>Text</color-r>
         */
        cli('Use different styles/colors to make terminal reading life easier');
        $availableColors = ['black', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white', 'default'];
        $listOfExamples = [];
        foreach ($availableColors as $color) {
            $listOfExamples["\<{$color}\>"] = implode(' ', [
                "<{$color}>Regular</{$color}>",
                "<{$color}-b>Bold</{$color}-b>",
                "<{$color}-u>Underlined</{$color}-u>",
                "<{$color}-bg>Background</{$color}-bg>",
                "<{$color}-r>Reverse</{$color}-r>",
                "<{$color}-bl>Blink</{$color}-bl>",
            ]);
        }
        cli(CliRender::list($listOfExamples, '*'));

        cli('Shortcuts:');
        cli(CliRender::list([
            '\<bl\>' => '<bl>Blink</bl>',
            '\<b\>'  => '<b>Bold</b>',
            '\<u\>'  => '<u>Underscore</u>',
            '\<r\>'  => '<r>Reverse</r>',
            '\<bg\>' => '<bg>Background</bg>',
        ], '*'));

        cli('Aliases:');
        cli(CliRender::list([
            '\<i\>' => '<i>Alias for \<info\></i>',
            '\<c\>' => '<c>Alias for \<commnet\></c>',
            '\<q\>' => '<q>Alias for \<question\></q>',
            '\<e\>' => '<e>Alias for \<error\></e>',
        ], '*'));


        // Default success exist code is "0". Max value is 255.
        return self::SUCCESS;
    }
}
