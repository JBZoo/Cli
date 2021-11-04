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

use JBZoo\Utils\Env;
use JBZoo\Utils\Sys;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CliHelper
 * @package JBZoo\Cli
 */
class CliHelper
{
    /**
     * @var $this
     */
    private static $instance;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var OutputInterface
     */
    private $errOutput;

    /**
     * @var float
     */
    private $startTimer;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->startTimer = microtime(true);
        $this->input = $input;
        $this->output = self::addOutputStyles($output);

        $errOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $this->errOutput = self::addOutputStyles($errOutput);

        self::$instance = $this;
    }

    /**
     * @return $this
     */
    public static function getInstance(): self
    {
        return self::$instance;
    }

    /**
     * @return InputInterface
     */
    public function getInput(): InputInterface
    {
        return $this->input;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * @return OutputInterface
     */
    public function getErrOutput(): OutputInterface
    {
        return $this->errOutput;
    }

    /**
     * @return string
     */
    public static function getRootPath(): string
    {
        $rootPath = defined('JBZOO_PATH_ROOT') ? (string)JBZOO_PATH_ROOT : null;
        if (!$rootPath) {
            return Env::string('JBZOO_PATH_ROOT');
        }

        return $rootPath;
    }

    /**
     * @return string
     */
    public static function getBinPath(): string
    {
        $binPath = defined('JBZOO_PATH_BIN') ? (string)JBZOO_PATH_BIN : null;
        if (!$binPath) {
            return Env::string('JBZOO_PATH_BIN');
        }

        return $binPath;
    }

    /**
     * @param OutputInterface $output
     * @return OutputInterface
     */
    public static function addOutputStyles(OutputInterface $output): OutputInterface
    {
        $formatter = $output->getFormatter();

        $colors = ['black', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white', 'default'];

        foreach ($colors as $color) {
            $formatter->setStyle($color, new OutputFormatterStyle($color));
            $formatter->setStyle("{$color}-blink", new OutputFormatterStyle($color, null, ['blink']));
            $formatter->setStyle("{$color}-bold", new OutputFormatterStyle($color, null, ['bold']));
            $formatter->setStyle("{$color}-under", new OutputFormatterStyle($color, null, ['underscore']));
            $formatter->setStyle("bg-{$color}", new OutputFormatterStyle(null, $color));
        }

        return $output;
    }

    /**
     * @return array
     */
    public function getProfileDate(): array
    {
        return [
            number_format(microtime(true) - $this->startTimer, 3),
            Sys::getMemory(false),
            Sys::getMemory(true)
        ];
    }
}
