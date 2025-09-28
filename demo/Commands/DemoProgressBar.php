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

use Faker\Factory;
use JBZoo\Cli\CliCommand;
use JBZoo\Cli\ProgressBars\ExceptionBreak;
use JBZoo\Utils\Slug;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputOption;

class DemoProgressBar extends CliCommand
{
    private const CASE_SIMPLE          = 'simple';
    private const CASE_MESSAGES        = 'messages';
    private const CASE_ARRAY           = 'array';
    private const CASE_BREAK           = 'break';
    private const CASE_EXCEPTION       = 'exception';
    private const CASE_EXCEPTION_LIST  = 'exception-list';
    private const CASE_MILLION         = 'million';
    private const CASE_MILLION_SYMFONY = 'million-symfony';

    protected function configure(): void
    {
        $this
            ->setName('progress-bar')
            ->setDescription('Examples of progress bar')
            ->addOption('case', 'c', InputOption::VALUE_REQUIRED, 'Case name.')
            ->addOption('no-sleep', 't', InputOption::VALUE_NONE, 'Disable sleep timer.');

        parent::configure();
    }

    protected function executeAction(): int
    {
        $listOfUsers = $this->prepareListOfDemoUsers();
        $caseName    = $this->getOptString('case', '', [
            self::CASE_SIMPLE,
            self::CASE_MESSAGES,
            self::CASE_ARRAY,
            self::CASE_BREAK,
            self::CASE_EXCEPTION,
            self::CASE_EXCEPTION_LIST,
            self::CASE_MILLION,
            self::CASE_MILLION_SYMFONY,
        ]);

        // Just 5 simple steps /////////////////////////////////////////////////////////////////////////////////////////
        if ($caseName === self::CASE_SIMPLE) {
            $this->progressBar(5, function (): void {
                $this->sleep();
            });
        }

        // Simple progress with custom message based on callback arguments /////////////////////////////////////////////
        if ($caseName === self::CASE_MESSAGES) {
            $this->progressBar($listOfUsers, function ($value, $key, $step) {
                $this->sleep();

                return "<c>Callback Args</c> \$value=<i>{$value}</i>, \$key=<i>{$key}</i>, \$step=<i>{$step}</i>";
            }, 'Custom messages based on callback arguments');
        }

        // Use the associated array as a data source ///////////////////////////////////////////////////////////////////
        if ($caseName === self::CASE_ARRAY) {
            dump($listOfUsers);
            $this->progressBar($listOfUsers, function ($value, $key, $step) {
                $this->sleep();

                return "<c>Callback Args</c> \$value=<i>{$value}</i>, \$key=<i>{$key}</i>, \$step=<i>{$step}</i>";
            }, 'Handling associated array as a data source');
        }

        // Exit the loop programmatically //////////////////////////////////////////////////////////////////////////////
        if ($caseName === self::CASE_BREAK) {
            dump($listOfUsers);
            $this->progressBar($listOfUsers, function ($value, $key, $step) {
                $this->sleep();
                if ($step === 3) {
                    throw new ExceptionBreak("Something went wrong with \$value={$value}");
                }

                return "<c>Callback Args</c> \$value=<i>{$value}</i>, \$key=<i>{$key}</i>, \$step=<i>{$step}</i>";
            }, 'Exit the loop programmatically');
        }

        // Exception handling //////////////////////////////////////////////////////////////////////////////////////////
        if ($caseName === self::CASE_EXCEPTION) {
            $this->progressBar(5, function ($value) {
                $this->sleep();
                if ($value === 1) {
                    throw new Exception("Something went really wrong on step #{$value}");
                }

                return "\$value=<i>{$value}</i>";
            }, 'Exception handling', false);
        }

        // Ignoring and collecting exceptions. Throw an error only at the end. /////////////////////////////////////////
        if ($caseName === self::CASE_EXCEPTION_LIST) {
            $this->progressBar(10, function ($value): void {
                $this->sleep();
                if ($value % 3 === 0) {
                    throw new Exception("Something went really wrong on step #{$value}");
                }
            }, 'Ignoring and collecting exceptions. Throw them only at the end.', true);
        }

        // Benchmark: 1 000 000 Items (~23 sec) ////////////////////////////////////////////////////////////////////////
        if ($caseName === self::CASE_MILLION) {
            $this->progressBar(
                1_000_000,
                static fn ($value, $key, $step) => '<c>Callback Args</c> '
                    . "\$value=<i>{$value}</i>, \$key=<i>{$key}</i>, \$step=<i>{$step}</i>",
                '1 000 000 items benchmark',
            );
        }

        // Benchmark: 1 000 000 Items with pure Symfony progress bar (~4 sec) //////////////////////////////////////////
        if ($caseName === self::CASE_MILLION_SYMFONY) {
            $this->_('Creates a new progress bar (1_000_000 units)');
            $progressBar = new ProgressBar($this->outputMode->getOutput(), 1_000_000);

            $this->_('Starts and displays the progress bar');
            $progressBar->start();

            $i = 0;

            while ($i++ < 1_000_000) {
                $progressBar->advance();
            }

            $this->_('Ensures that the progress bar is at 100%');
            $progressBar->finish();
        }

        return self::SUCCESS;
    }

    private function prepareListOfDemoUsers(): array
    {
        $faker = Factory::create();

        $users = [];

        for ($i = 0; $i < 5; $i++) {
            $firstName = $faker->firstName;
            $lastName  = $faker->lastName;
            $email     = Slug::filter($firstName) . '@site.com';

            $users[$email] = $firstName . ' ' . $lastName;
        }

        return $users;
    }

    private function sleep(): void
    {
        if ($this->getOptBool('no-sleep')) {
            return;
        }

        \usleep(\random_int(500, 1200) * 1000);
    }
}
