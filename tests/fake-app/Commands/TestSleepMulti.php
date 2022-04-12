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
use JBZoo\Cli\Exception;
use JBZoo\Cli\Cli;
use JBZoo\Cli\OutLvl;
use JBZoo\Utils\Env;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function JBZoo\Data\json;

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
            ->addOption('random-sleep', null, InputOption::VALUE_NONE, 'Random sleep timer (1-5)')
            ->addArgument('arg-1', InputArgument::OPTIONAL, 'Custom Agrument #1', 'QWERTY-1')
            ->addArgument('arg-2', InputArgument::OPTIONAL, 'Custom Agrument #2', 'QWERTY-2')
            ->addArgument('arg-3', InputArgument::OPTIONAL, 'Custom Agrument #3', 'QWERTY-3');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeOneProcess(string $pmThreadId): int
    {
        $sleep = $this->getOptInt('sleep');
        if ($this->getOptBool('random-sleep')) {
            $sleep = random_int(1, 5);
        }

        if ($sleep === 2 && $pmThreadId === '2') {
            throw new Exception('Exception messsage');
        }

        $this->_([
            "Started: {$pmThreadId}",
            'Sleep : ' . $sleep,
            'Arg #1: ' . $this->helper->getInput()->getArgument('arg-1'),
            'Arg #2: ' . $this->helper->getInput()->getArgument('arg-2'),
            'Arg #3: ' . $this->helper->getInput()->getArgument('arg-3'),
            'Env Var: ' . Env::string('JBZOO_TEST_VAR'),
        ]);

        sleep($sleep);

        $this->_("Finished: {$pmThreadId}", OutLvl::Q);

        return 0;
    }

    /**
     * @return string[]
     */
    protected function getListOfChildIds(): array
    {
        return ['1', '2', '3', '4', '5'];
    }

    /**
     * @param array $procPool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @phan-suppress PhanPluginPossiblyStaticProtectedMethod
     * @phan-suppress PhanUnusedProtectedNoOverrideMethodParameter
     */
    protected function afterFinishAllProcesses(array $procPool): void
    {
        $result = [];
        foreach ($procPool as $procId => $procInfo) {
            $result[] = $procInfo['std_out'];
        }

        $this->_(json($result));
    }
}
