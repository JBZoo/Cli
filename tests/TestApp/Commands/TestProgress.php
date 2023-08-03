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

namespace JBZoo\PHPUnit\TestApp\Commands;

use JBZoo\Cli\CliCommand;
use JBZoo\Cli\Exception;
use JBZoo\Cli\ProgressBars\ProgressBar;
use Symfony\Component\Console\Input\InputOption;

use function JBZoo\Data\json;

class TestProgress extends CliCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('test:progress')
            ->addOption('case', null, InputOption::VALUE_REQUIRED)
            ->addOption('batch-exception', 'b', InputOption::VALUE_NONE)
            ->addOption('sleep', 's', InputOption::VALUE_OPTIONAL);

        parent::configure();
    }

    protected function executeAction(): int
    {
        $testCase = $this->getOptString('case');

        if ($testCase === 'no-items-int') {
            // Static call as backwards capability
            ProgressBar::run(0, static function (): void {
            }, $testCase);
        }

        if ($testCase === 'no-items-array') {
            $this->progressBar([], static function (): void {
            }, $testCase);
        }

        if ($testCase === 'no-items-data') {
            $this->progressBar(json(), static function (): void {
            }, $testCase);
        }

        if ($testCase === 'minimal') {
            $this->progressBar(2, function (): void {
                \sleep($this->getOptInt('sleep'));
            });
        }

        if ($testCase === 'no-messages') {
            $this->progressBar(3, static function ($stepValue, $stepIndex, $currentStep): void {
            }, $testCase);
        }

        if ($testCase === 'one-message') {
            $this->progressBar(3, static function ($stepValue, $stepIndex, $currentStep) {
                if ($stepValue === 1) {
                    return "{$stepValue}, {$stepIndex}, {$currentStep}";
                }
            }, $testCase);
        }

        if ($testCase === 'simple-message-all') {
            $this->progressBar(
                3,
                static fn ($stepValue, $stepIndex, $currentStep) => "{$stepValue}, {$stepIndex}, {$currentStep}",
                $testCase,
            );
        }

        if ($testCase === 'output-as-array') {
            $list = ['key_1' => 'value_1', 'key_2' => 'value_2'];
            $this->progressBar(
                $list,
                static fn ($stepValue, $stepIndex, $currentStep) => [$stepValue, $stepIndex, $currentStep],
                $testCase,
            );
        }

        if ($testCase === 'array-int') {
            $this->progressBar(
                [4, 5, 6],
                static fn ($stepValue, $stepIndex, $currentStep) => "{$stepValue}, {$stepIndex}, {$currentStep}",
                $testCase,
            );
        }

        if ($testCase === 'array-string') {
            $this->progressBar(
                ['qwerty', 'asdfgh'],
                static fn ($stepValue, $stepIndex, $currentStep) => "{$stepValue}, {$stepIndex}, {$currentStep}",
                $testCase,
            );
        }

        if ($testCase === 'array-assoc') {
            $list = ['key_1' => 'value_1', 'key_2' => 'value_2'];
            $this->progressBar(
                $list,
                static fn ($stepValue, $stepIndex, $currentStep) => "{$stepValue}, {$stepIndex}, {$currentStep}",
                $testCase,
            );
        }

        if ($testCase === 'data') {
            $list = json(['key_1' => 'value_1', 'key_2' => 'value_2']);
            $this->progressBar(
                $list,
                static fn ($stepValue, $stepIndex, $currentStep) => "{$stepValue}, {$stepIndex}, {$currentStep}",
                $testCase,
            );
        }

        if ($testCase === 'break') {
            $this->progressBar(3, static function ($stepValue) {
                if ($stepValue === 1) {
                    return ProgressBar::BREAK;
                }

                return $stepValue;
            }, $testCase);
        }

        if ($testCase === 'exception') {
            $this->progressBar(3, static function ($stepValue): void {
                if ($stepValue === 1) {
                    throw new \Exception("Exception #{$stepValue}");
                }
            }, $testCase, $this->getOptBool('batch-exception'));
        }

        if ($testCase === 'exception-list') {
            $this->progressBar(10, static function ($stepValue): void {
                if ($stepValue % 3 === 0) {
                    throw new \RuntimeException("Exception #{$stepValue}");
                }
            }, $testCase, $this->getOptBool('batch-exception'));
        }

        if ($testCase === 'million-items') {
            $this->progressBar(100000, static fn ($stepValue) => $stepValue, $testCase);
        }

        if ($testCase === 'memory-leak') {
            $array = [];
            $this->progressBar(3, function () use (&$array): void {
                for ($i = 0; $i < 100000; $i++) {
                    $array[] = $i;
                }

                \sleep($this->getOptInt('sleep'));
            }, $testCase);
        }

        if ($testCase === 'nested') {
            $array = [];

            $this->progressBar(3, function ($parentId) use ($testCase) {
                \sleep($this->getOptInt('sleep'));

                $this->progressBar(4, function ($childId) use ($parentId) {
                    \sleep($this->getOptInt('sleep'));

                    return "out_child_{$parentId}_{$childId}";
                }, "{$testCase}_child_{$parentId}", false);

                return "out_parent_{$parentId}";
            }, "{$testCase}_parent", false);
        }

        if (!$testCase) {
            throw new Exception('undefined --case');
        }

        return 0;
    }
}
