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
use JBZoo\Cli\ProgressBars\ProgressBarSymfony;
use Symfony\Component\Console\Input\InputOption;

class ExamplesProgressBar extends CliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('examples:progress-bar')
            ->setDescription('Examples of progress bar')
            ->addOption('exception', null, InputOption::VALUE_NONE, 'Throw exception')
            ->addOption('exception-list', null, InputOption::VALUE_NONE, 'Throw list of exceptions');

        parent::configure();
    }

    protected function executeAction(): int
    {
        // ////////////////////////////////////////////////////////////////////// Just 3 steps
        $this->progressBar(2, static function ($stepValue, $stepIndex, $currentStep) {
            \sleep(1);

            return "Step info: \$stepValue={$stepValue}, \$stepIndex={$stepIndex}, \$currentStep={$currentStep}";
        }, 'Number of steps');

        // ////////////////////////////////////////////////////////////////////// Assoc array. Step-by-step
        $list = [
            'key_1' => 'value_1',
            'key_2' => 'value_2',
            'key_3' => 'value_3',
        ];

        $this->progressBar(
            $list,
            static fn (
                $stepValue,
                $stepIndex,
                $currentStep,
            ) => "Step info: \$stepValue={$stepValue}, \$stepIndex={$stepIndex}, \$currentStep={$currentStep}",
            'Assoc array',
        );

        // ////////////////////////////////////////////////////////////////////// Exit from the cycle
        $this->progressBar(3, static function ($stepValue, $stepIndex, $currentStep) {
            if ($stepValue === 1) {
                return ProgressBarSymfony::BREAK;
            }

            return "Step info: \$stepValue={$stepValue}, \$stepIndex={$stepIndex}, \$currentStep={$currentStep}";
        }, 'Exit from the cycle');

        // ////////////////////////////////////////////////////////////////////// Exception
        if ($this->getOptBool('exception')) {
            $this->progressBar(3, static function ($stepValue) {
                if ($stepValue === 1) {
                    throw new Exception("Exception #{$stepValue}");
                }

                return "\$stepValue={$stepValue}";
            }, 'Exception handling', false);
        }

        // ////////////////////////////////////////////////////////////////////// List of Exceptions
        if ($this->getOptBool('exception-list')) {
            $this->progressBar(10, static function ($stepValue) {
                if ($stepValue % 3 === 0) {
                    throw new Exception("Exception #{$stepValue}");
                }

                return "\$stepValue={$stepValue}";
            }, 'Handling list of exceptions at once', true);
        }

        // Default success exist code is "0". Max value is 255.
        return self::SUCCESS;
    }
}
