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
use JBZoo\Cli\ProgressBar;
use JBZoo\Cli\Exception;
use Symfony\Component\Console\Input\InputOption;

use function JBZoo\Data\json;

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
            ->addOption('case', null, InputOption::VALUE_OPTIONAL)
            ->addOption('batch-exception', 'b', InputOption::VALUE_NONE);

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeAction(): int
    {
        $testCase = $this->getOptString('case');

        if ($testCase === 'no-items-int') {
            ProgressBar::run(0, function () {
            }, $testCase);
        }

        if ($testCase === 'no-items-array') {
            ProgressBar::run([], function () {
            }, $testCase);
        }

        if ($testCase === 'no-items-data') {
            ProgressBar::run(json(), function () {
            }, $testCase);
        }

        if ($testCase === 'minimal') {
            ProgressBar::run(3, function () {
                sleep(1);
            }, $testCase);
        }

        if ($testCase === 'no-messages') {
            ProgressBar::run(3, function ($stepValue, $stepIndex, $currentStep) {
            }, $testCase);
        }

        if ($testCase === 'one-message') {
            ProgressBar::run(3, function ($stepValue, $stepIndex, $currentStep) {
                if ($stepValue === 1) {
                    return "{$stepValue}, {$stepIndex}, {$currentStep}";
                }
            }, $testCase);
        }

        if ($testCase === 'simple-message-all') {
            ProgressBar::run(3, function ($stepValue, $stepIndex, $currentStep) {
                return "{$stepValue}, {$stepIndex}, {$currentStep}";
            }, $testCase);
        }

        if ($testCase === 'array-int') {
            ProgressBar::run([4, 5, 6], function ($stepValue, $stepIndex, $currentStep) {
                return "{$stepValue}, {$stepIndex}, {$currentStep}";
            }, $testCase);
        }

        if ($testCase === 'array-string') {
            ProgressBar::run(['qwerty', 'asdfgh'], function ($stepValue, $stepIndex, $currentStep) {
                return "{$stepValue}, {$stepIndex}, {$currentStep}";
            }, $testCase);
        }

        if ($testCase === 'array-assoc') {
            $list = ['key_1' => 'value_1', 'key_2' => 'value_2'];
            ProgressBar::run($list, function ($stepValue, $stepIndex, $currentStep) {
                return "{$stepValue}, {$stepIndex}, {$currentStep}";
            }, $testCase);
        }

        if ($testCase === 'data') {
            $list = json(['key_1' => 'value_1', 'key_2' => 'value_2']);
            ProgressBar::run($list, function ($stepValue, $stepIndex, $currentStep) {
                return "{$stepValue}, {$stepIndex}, {$currentStep}";
            }, $testCase);
        }

        if ($testCase === 'break') {
            ProgressBar::run(3, function ($stepValue) {
                if ($stepValue === 1) {
                    return ProgressBar::BREAK;
                }
                return $stepValue;
            }, $testCase);
        }

        if ($testCase === 'exception') {
            ProgressBar::run(3, function ($stepValue) {
                if ($stepValue === 1) {
                    throw new \Exception("Exception #{$stepValue}");
                }
            }, $testCase, $this->getOptBool('batch-exception'));
        }

        if ($testCase === 'exception-list') {
            ProgressBar::run(10, function ($stepValue) {
                if ($stepValue % 3 === 0) {
                    throw new \Exception("Exception #{$stepValue}");
                }
            }, $testCase, $this->getOptBool('batch-exception'));
        }

        if ($testCase === 'million-items') {
            ProgressBar::run(100000, function ($stepValue) {
                return $stepValue;
            }, $testCase);
        }

        if ($testCase === 'memory-leak') {
            $array = [];
            ProgressBar::run(3, function () use (&$array) {
                for ($i = 0; $i < 100000; $i++) {
                    $array[] = $i;
                }

                sleep(1);
            }, $testCase);
        }

        if (!$testCase) {
            throw new Exception('undefined --test-case');
        }

        return 0;
    }
}
