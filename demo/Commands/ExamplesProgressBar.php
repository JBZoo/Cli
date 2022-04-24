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
use JBZoo\Cli\ProgressBars\ProgressBar;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ExamplesProgressBar
 */
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

    /**
     * @inheritDoc
     */
    protected function executeAction(): int
    {
        //////////////////////////////////////////////////////////////////////// Just 3 steps
        ProgressBar::run(2, function ($stepValue, $stepIndex, $currentStep) {
            sleep(1);
            return "Step info: \$stepValue={$stepValue}, \$stepIndex={$stepIndex}, \$currentStep={$currentStep}";
        }, 'Number of steps');


        //////////////////////////////////////////////////////////////////////// Assoc array. Step-by-step
        $list = [
            'key_1' => 'value_1',
            'key_2' => 'value_2',
            'key_3' => 'value_3'
        ];
        ProgressBar::run($list, function ($stepValue, $stepIndex, $currentStep) {
            return "Step info: \$stepValue={$stepValue}, \$stepIndex={$stepIndex}, \$currentStep={$currentStep}";
        }, 'Assoc array');


        //////////////////////////////////////////////////////////////////////// Exit from the cycle
        ProgressBar::run(3, function ($stepValue, $stepIndex, $currentStep) {
            if ($stepValue === 1) {
                return ProgressBar::BREAK;
            }
            return "Step info: \$stepValue={$stepValue}, \$stepIndex={$stepIndex}, \$currentStep={$currentStep}";
        }, 'Exit from the cycle');


        //////////////////////////////////////////////////////////////////////// Exception
        if ($this->getOptBool('exception')) {
            ProgressBar::run(3, function ($stepValue) {
                if ($stepValue === 1) {
                    throw new Exception("Exception #{$stepValue}");
                }
                return "\$stepValue={$stepValue}";
            }, 'Exception handling', false);
        }

        //////////////////////////////////////////////////////////////////////// List of Exceptions
        if ($this->getOptBool('exception-list')) {
            ProgressBar::run(10, function ($stepValue) {
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
