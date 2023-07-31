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

namespace JBZoo\Cli\OutputMods;

use JBZoo\Cli\CliApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Cron extends Text
{
    public const NAME        = 'cron';
    public const DESCRIPTION = "Shortcut for crontab. It's basically focused on logs output.\n"
    . " It's combination of <info>--timestamp --profile --stdout-only --no-progress -vv</info>.";

    public function __construct(InputInterface $input, OutputInterface $output, CliApplication $application)
    {
        parent::__construct($input, $output, $application);

        $this->output->getFormatter()->setDecorated(false);
        if ($this->output->getVerbosity() < OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $this->output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        }

        $this->errOutput->getFormatter()->setDecorated(false);
        if ($this->errOutput->getVerbosity() < OutputInterface::VERBOSITY_VERY_VERBOSE) {
            //$this->errOutput->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        }
    }

    public function isStdoutOnly(): bool
    {
        return true;
    }

    public function isDisplayProfiling(): bool
    {
        return true;
    }

    public function isDisplayTimestamp(): bool
    {
        return true;
    }

    public function isInfoLevel(): bool
    {
        return true;
    }

    public function isWarningLevel(): bool
    {
        return true;
    }

    public function isProgressBarDisabled(): bool
    {
        return true;
    }
}
