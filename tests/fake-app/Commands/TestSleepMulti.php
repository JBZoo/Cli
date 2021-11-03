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

use JBZoo\Cli\CliCommandMultiProc;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class TestSleepMulti
 * @package JBZoo\TestApp\Commands
 */
class TestSleepMulti extends CliCommandMultiProc
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this
            ->setName('test:sleep-multi')
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, 'Timeout in seconds to sleep the PHP script', 5)
            ->addArgument('arg-1', InputArgument::REQUIRED, 'Custom Agrument #1')
            ->addArgument('arg-2', InputArgument::REQUIRED, 'Custom Agrument #2')
            ->addArgument('arg-3', InputArgument::OPTIONAL, 'Custom Agrument #3', 'qwerty');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeOneProcessAction(string $pmThreadId): int
    {
        $this->_([
            "Started: {$pmThreadId}",
            'Sleep : ' . $this->getOptString('sleep'),
            'Arg #1: ' . $this->input->getArgument('arg-1'),
            'Arg #2: ' . $this->input->getArgument('arg-2'),
        ]);

        sleep($this->getOptInt('sleep'));

        $this->_("Finished: {$pmThreadId}");

        return 0;
    }

    /**
     * @return string[]
     */
    protected function getListOfChildIds(): array
    {
        return ['1', '2'];
    }

    /**
     * @inheritDoc
     */
    protected function afterFinishAllProcesses(array $procPool): void
    {
        dump($procPool['82']);
    }
}
