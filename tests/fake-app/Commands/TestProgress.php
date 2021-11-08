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
use JBZoo\Cli\CliProgressBar;
use Symfony\Component\Console\Input\InputOption;

/**
 *
 */
class TestProgress extends CliCommand
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this
            ->setName('test:progress')
            ->addOption('test-case', null, InputOption::VALUE_OPTIONAL);

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeAction(): int
    {
        $testCase = $this->getOptString('test-case');

        if ($testCase === 'no-items') {
            CliProgressBar::run(0, function () {
            }, $testCase);
        }

        if ($testCase === 'minimal') {
            CliProgressBar::run(1, function () {
                sleep(1);
            }, $testCase);
        }

        if ($testCase === 'memory-leak') {
            $array = [];
            CliProgressBar::run(3, function () use (&$array) {
                for ($i = 0; $i < 100000; $i++) {
                    $array[] = $i;
                }

                sleep(1);
            }, $testCase);
        }

        // $stepValue, $stepIndex, $currentStep

        return 0;
    }
}
