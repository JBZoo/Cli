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

namespace JBZoo\Cli;

use JBZoo\Utils\Arr;
use JBZoo\Utils\Sys;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function JBZoo\Utils\bool;
use function JBZoo\Utils\float;
use function JBZoo\Utils\int;

/**
 * Class CliCommand
 * @package JBZoo\Cli
 */
abstract class CliCommand extends Command
{
    /**
     * @var InputInterface
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected $input;

    /**
     * @var OutputInterface
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected $output;

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $startTime = microtime(true);
        $result = $this->executeAction();
        $finishTime = microtime(true);

        if ($this->isDebug()) {
            $totalTime = number_format($finishTime - $startTime, 3);
            $curMemory = Sys::getMemory(false);
            $maxMemory = Sys::getMemory(true);

            $this->_([
                '---- ---- ---- ---- ---- ---- ---- ---- ----',
                "* Execution Time  : {$totalTime} sec;",
                "* Memory (cur/max): {$curMemory} / {$maxMemory};",
            ]);
        }

        return $result;
    }

    /**
     * @return int
     */
    abstract protected function executeAction(): int;

    /**
     * @param string $optionName
     * @param bool   $canBeArray
     * @return mixed|null
     */
    protected function getOpt(string $optionName, bool $canBeArray = true)
    {
        $value = $this->input->getOption($optionName);

        if ($canBeArray && is_array($value)) {
            return Arr::last($value);
        }

        return $value;
    }

    /**
     * @param string $optionName
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    protected function getOptBool(string $optionName): bool
    {
        $value = $this->getOpt($optionName);
        return bool($value);
    }

    /**
     * @param string $optionName
     * @return int
     */
    protected function getOptInt(string $optionName): int
    {
        $value = $this->getOpt($optionName) ?? 0;
        return int($value);
    }

    /**
     * @param string $optionName
     * @return float
     */
    protected function getOptFloat(string $optionName): float
    {
        $value = $this->getOpt($optionName) ?? 0.0;
        return float($value);
    }

    /**
     * @param string $optionName
     * @return string
     */
    protected function getOptString(string $optionName): string
    {
        $value = $this->getOpt($optionName) ?? '';
        return (string)$value;
    }

    /**
     * @param string $optionName
     * @return array
     */
    protected function getOptArray(string $optionName): array
    {
        $list = $this->getOpt($optionName, false) ?? [];
        return (array)$list;
    }

    /**
     * @return string|null
     */
    protected static function getStdIn(): ?string
    {
        // It can be read only once, so we save result as internal varaible
        static $result;

        if (null === $result) {
            $result = '';
            while (!feof(STDIN)) {
                $result .= fread(STDIN, 1024);
            }
        }

        return $result;
    }

    /**
     * Alias to write new line
     * @param string|array $messages
     * @param string       $verboseLevel
     * @param bool         $newline
     * @return void
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _($messages, string $verboseLevel = '', bool $newline = true): void
    {
        $verboseLevel = \strtolower(\trim($verboseLevel));

        if ($verboseLevel === 'vvv') {
            $this->output->write($messages, $newline, OutputInterface::VERBOSITY_DEBUG);
        } elseif ($verboseLevel === 'vv') {
            $this->output->write($messages, $newline, OutputInterface::VERBOSITY_VERY_VERBOSE);
        } elseif ($verboseLevel === 'v') {
            $this->output->write($messages, $newline, OutputInterface::VERBOSITY_VERBOSE);
        } elseif ($verboseLevel === '') {
            $this->output->write($messages, $newline, OutputInterface::VERBOSITY_NORMAL);
        } elseif ($verboseLevel === 'q') {
            $this->output->write($messages, $newline, OutputInterface::VERBOSITY_QUIET);
        } elseif ($verboseLevel === 'debug') {
            $this->_('Debug: ' . $messages, 'vvv');
        } elseif ($verboseLevel === 'warn') {
            $this->_('<comment>Warn:</comment> ' . $messages, 'vv');
        } elseif ($verboseLevel === 'info') {
            $this->_('<info>Info:</info> ' . $messages, 'v');
        } elseif ($verboseLevel === 'error') {
            $this->_('<error>Error:</error> ' . $messages, 'q');
        }
    }

    /**
     * @return bool
     */
    protected function isDebug(): bool
    {
        return $this->output->getVerbosity() === OutputInterface::VERBOSITY_DEBUG;
    }
}
