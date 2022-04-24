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
 * Class ExamplesHelpers
 */
class ExamplesHelpers extends CliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('examples:helpers')
            ->setDescription('Examples of new CLI helpers');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeAction(): int
    {
        $yourName = $this->ask("What's your name?", 'idk');
        cli("Your name is \"{$yourName}\"");

        $yourSecret = $this->askPassword("New password?");
        cli("Your secret is \"{$yourSecret}\"");

        $selectedColor = $this->askOption('Choose your favorite color', ['Red', 'Blue', 'Yellow'], 1);
        $colorAlias = strtolower($selectedColor);
        cli("Selected color is \"<{$colorAlias}>{$selectedColor}</{$colorAlias}>\"");

        $isConfirmed = $this->confirmation('Are you ready to execute the script?');
        cli("Is confirmed: " . ($isConfirmed ? 'Yes' : 'No'));

        return self::SUCCESS;
    }
}
