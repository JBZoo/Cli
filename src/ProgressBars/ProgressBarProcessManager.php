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

namespace JBZoo\Cli\ProgressBars;

use JBZoo\Cli\CliRender;
use JBZoo\Cli\Icons;
use JBZoo\Cli\ProgressBars\AbstractProgressBar;
use Symfony\Component\Console\Helper\ProgressBar as SymfonyProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ProgressBarProcessManager extends AbstractProgressBar
{
    private OutputInterface $output;

    private SymfonyProgressBar $progressBar;

    public function __construct(OutputInterface $output, int $maxCount = 0)
    {
        $this->output      = $output;
        $this->progressBar = $this->createProgressBar($output, $maxCount);
    }

    public function start(): void
    {
        $this->progressBar->start();
    }

    public function finish(): void
    {
        $this->progressBar->finish();
    }

    public function advance(): void
    {
        $this->progressBar->advance();
    }

    protected function buildTemplate(): string
    {
        $this->configureProgressBar();
        $finishIcon = Icons::getRandomIcon(Icons::GROUP_FINISH, $this->output->isDecorated());

        $progressBarLines = [
            '%percent:2s%%',
            '(%current% / %max%)',
            '[%bar%]',
            $finishIcon,
            '%jbzoo_memory_current:8s%',
        ];

        $footerLine = [
            'Time (pass/left/est)' => \implode(' / ', [
                '%jbzoo_time_elapsed:8s%',
                '<info>%jbzoo_time_remaining:8s%</info>',
                '%jbzoo_time_estimated%',
            ]),

            'Caught exceptions' => '%jbzoo_caught_exceptions%',
        ];

        return \implode(' ', $progressBarLines) . "\n" . CliRender::list($footerLine) . "\n";
    }

    private function createProgressBar(OutputInterface $output, int $maxCount = 0): SymfonyProgressBar
    {
        $progressBar = new SymfonyProgressBar($output, $maxCount);

        $progressBar->setBarCharacter('<green>•</green>');
        $progressBar->setEmptyBarCharacter('<yellow>_</yellow>');
        $progressBar->setProgressCharacter(Icons::getRandomIcon(Icons::GROUP_PROGRESS, $this->output->isDecorated()));
        $progressBar->setBarWidth(40);
        $progressBar->setFormat($this->buildTemplate());

        $progressBar->setMessage('n/a');
        $progressBar->setMessage('0', 'jbzoo_caught_exceptions');
        $progressBar->setProgress(0);
        $progressBar->setOverwrite(true);

        $progressBar->setRedrawFrequency(1);
        $progressBar->minSecondsBetweenRedraws(0.5);
        $progressBar->maxSecondsBetweenRedraws(1.5);

        return $progressBar;
    }
}
