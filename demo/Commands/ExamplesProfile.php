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

namespace DemoApp\Commands;

use JBZoo\Cli\CliCommand;

use function JBZoo\Cli\cli;

/**
 * Class ExamplesProfile
 */
class ExamplesProfile extends CliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('examples:profile')
            ->setDescription('Examples of memory and time profiling');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeAction(): int
    {
        cli('Start cycles');

        $parentMaxStep = 3;
        $childMaxStep = 10 ^ 6;

        for ($parentIndex = 0; $parentIndex < $parentMaxStep; $parentIndex++) {
            $array = [];
            for ($index = 0; $index < $childMaxStep; $index++) {
                $array[] = $index;
            }

            cli("Iteration: {$parentIndex}");
            unset($array);
            usleep(random_int(10000, 300000));
        }

        cli('Finish cycles');

        // Default success exist code is "0". Max value is 255.
        return self::SUCCESS;
    }
}
