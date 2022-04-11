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
use JBZoo\Utils\FS;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Helper
 * @package JBZoo\Cli
 */
class Helper
{
    public const VERB_QUIET = 'q';

    public const VERB_DEFAULT = '';
    public const VERB_V       = 'v';
    public const VERB_VV      = 'vv';
    public const VERB_VVV     = 'vvv';
    public const VERB_E       = 'e';

    public const VERB_DEBUG     = 'debug';
    public const VERB_INFO      = 'info';
    public const VERB_WARNING   = 'warning';
    public const VERB_ERROR     = 'error';
    public const VERB_EXCEPTION = 'exception';

    public const TIMESTAMP_FORMAT = 'Y-m-d H:i:s.v P';

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
     * @var bool
     */
    private $outputHasErrors = false;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->startTimer = \microtime(true);
        $this->input = $input;
        $this->output = self::addOutputStyles($output);

        $errOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $errOutput = self::addOutputStyles($errOutput);

        if ($this->input->getOption('stdout-only')) {
            $this->errOutput = $this->output;
            if ($this->output instanceof ConsoleOutput) {
                $this->output->setErrorOutput($this->output);
            }
        } else {
            $this->errOutput = $errOutput;
        }

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
        $rootPath = \defined('JBZOO_PATH_ROOT') ? (string)\JBZOO_PATH_ROOT : null;
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
        $binPath = \defined('JBZOO_PATH_BIN') ? (string)\JBZOO_PATH_BIN : null;
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
            $formatter->setStyle("{$color}-bg", new OutputFormatterStyle(null, $color));
        }

        return $output;
    }

    /**
     * @return array
     */
    public function getProfileInfo(): array
    {
        return [
            \number_format(\microtime(true) - $this->startTimer, 3),
            FS::format(\memory_get_usage(false)),
            FS::format(\memory_get_peak_usage(false)),
        ];
    }

    /**
     * Alias to write new line in std output
     *
     * @param string|array $messages
     * @param string       $verboseLevel
     * @return void
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _($messages, string $verboseLevel = self::VERB_DEFAULT): void
    {
        $verboseLevel = \strtolower(\trim($verboseLevel));

        if (\is_array($messages)) {
            foreach ($messages as $message) {
                $this->_($message, $verboseLevel);
            }
            return;
        }

        $profilePrefix = '';

        if ($this->input->getOption('timestamp')) {
            $timestamp = (new \DateTimeImmutable())->format(self::TIMESTAMP_FORMAT);
            $profilePrefix .= "<green>[</green>{$timestamp}<green>]</green> ";
        }

        if ($this->input->getOption('profile')) {
            [$totalTime, $curMemory] = $this->getProfileInfo();
            $profilePrefix .= "<green>[</green>{$curMemory}<green>/</green>{$totalTime}s<green>]</green> ";
        }

        $vNormal = OutputInterface::VERBOSITY_NORMAL;

        if ($verboseLevel === self::VERB_DEFAULT) {
            $this->output->writeln($profilePrefix . $messages, $vNormal);
        } elseif ($verboseLevel === self::VERB_V) {
            $this->output->writeln($profilePrefix . $messages, OutputInterface::VERBOSITY_VERBOSE);
        } elseif ($verboseLevel === self::VERB_VV) {
            $this->output->writeln($profilePrefix . $messages, OutputInterface::VERBOSITY_VERY_VERBOSE);
        } elseif ($verboseLevel === self::VERB_VVV) {
            $this->output->writeln($profilePrefix . $messages, OutputInterface::VERBOSITY_DEBUG);
        } elseif ($verboseLevel === self::VERB_QUIET) {
            $this->output->writeln($profilePrefix . $messages, OutputInterface::VERBOSITY_QUIET); // Show ALWAYS!
        } elseif ($verboseLevel === self::VERB_DEBUG) {
            $this->_('<magenta>Debug:</magenta> ' . $messages, self::VERB_VVV);
        } elseif ($verboseLevel === self::VERB_WARNING) {
            $this->_('<yellow>Warning:</yellow> ' . $messages, self::VERB_VV);
        } elseif ($verboseLevel === self::VERB_INFO) {
            $this->_('<blue>Info:</blue> ' . $messages, self::VERB_V);
        } elseif ($verboseLevel === self::VERB_E) {
            $this->outputHasErrors = true;
            $this->errOutput->writeln($profilePrefix . $messages, $vNormal);
        } elseif ($verboseLevel === self::VERB_ERROR) {
            $this->outputHasErrors = true;
            $this->errOutput->writeln($profilePrefix . '<red-bg>Error:</red-bg> ' . $messages, $vNormal);
        } elseif ($verboseLevel === self::VERB_EXCEPTION) {
            $this->outputHasErrors = true;
            $this->errOutput->writeln($profilePrefix . '<red-bg>Muted Exception:</red-bg> ' . $messages, $vNormal);
        } else {
            throw new Exception("Undefined verbose level: \"{$verboseLevel}\"");
        }
    }

    /**
     * @return bool
     */
    public function isOutputHasErrors(): bool
    {
        return $this->outputHasErrors;
    }

    /**
     * @param array $items
     * @return int
     */
    public static function findMaxLength(array $items): int
    {
        $result = 0;
        foreach ($items as $item) {
            $tmpMax = \strlen($item);
            if ($result < $tmpMax) {
                $result = $tmpMax;
            }
        }

        return $result;
    }

    /**
     * @param array       $metrics
     * @param string|null $addDot
     * @return string
     */
    public static function renderList(array $metrics, ?string $addDot = null): string
    {
        $maxLength = self::findMaxLength(\array_keys($metrics));
        $lines = [];

        foreach ($metrics as $metricKey => $metricTmpl) {
            $currentLength = \strlen((string)$metricKey);
            $lines[] = \implode('', [
                $addDot ? " {$addDot} " : '',
                $metricKey,
                \str_repeat(' ', $maxLength - $currentLength),
                ': ',
                \implode('; ', (array)$metricTmpl)
            ]);
        }

        return \implode("\n", \array_filter($lines)) . "\n";
    }
}
