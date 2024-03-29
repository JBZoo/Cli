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

namespace JBZoo\Cli;

use JBZoo\Cli\OutputMods\AbstractOutputMode;
use JBZoo\Utils\Env;
use JBZoo\Utils\Str;

use function JBZoo\Utils\int;
use function JBZoo\Utils\isStrEmpty;

class CliHelper
{
    private static ?AbstractOutputMode $outputMode = null;

    public static function arrayMergeRecursiveDistinct(array &$array1, array &$array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (\is_array($value) && isset($merged[$key]) && \is_array($merged[$key])) {
                $merged[$key] = self::arrayMergeRecursiveDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    public static function getRootPath(): string
    {
        $rootPath = \defined('JBZOO_PATH_ROOT') ? (string)JBZOO_PATH_ROOT : null;
        if (isStrEmpty($rootPath)) {
            return Env::string('JBZOO_PATH_ROOT');
        }

        return (string)$rootPath;
    }

    public static function getBinPath(): string
    {
        $binPath = \defined('JBZOO_PATH_BIN') ? (string)JBZOO_PATH_BIN : null;
        if (isStrEmpty($binPath)) {
            return Env::string('JBZOO_PATH_BIN');
        }

        return (string)$binPath;
    }

    /**
     * @see https://github.com/phpstan/phpstan-src/blob/f8be122188/src/Process/CpuCoreCounter.php
     */
    public static function getNumberOfCpuCores(): int
    {
        static $numberOfCpuCores = null;

        if ($numberOfCpuCores !== null) {
            return $numberOfCpuCores;
        }

        if (!\function_exists('proc_open')) {
            return $numberOfCpuCores = 1;
        }

        // from brianium/paratest
        // Linux (and potentially Windows with linux sub systems)
        if (\is_file('/proc/cpuinfo')) {
            $cpuinfo = \file_get_contents('/proc/cpuinfo');
            if ($cpuinfo !== false) {
                \preg_match_all('/^processor/m', $cpuinfo, $matches);

                return $numberOfCpuCores = \count($matches[0]);
            }
        }

        // Windows
        if (\DIRECTORY_SEPARATOR === '\\') {
            $process = \popen('wmic cpu get NumberOfLogicalProcessors', 'rb');
            if (\is_resource($process)) {
                /** @phan-suppress-next-line PhanPluginUseReturnValueInternalKnown */
                \fgets($process);
                $cores = int(\fgets($process));
                \pclose($process);

                return $numberOfCpuCores = $cores;
            }
        }

        // *nix (Linux, BSD and Mac)
        $process = \popen('sysctl -n hw.ncpu', 'rb');
        if (\is_resource($process)) {
            $cores = int(\fgets($process));
            \pclose($process);

            return $numberOfCpuCores = $cores;
        }

        return $numberOfCpuCores = 2;
    }

    public static function renderListForHelpDescription(array $keyValues): string
    {
        $result = '';

        foreach ($keyValues as $key => $value) {
            $result .= "<comment>{$key}</comment> - {$value}\n";
        }

        return $result;
    }

    public static function createOrGetTraceId(): string
    {
        static $traceId = null;

        if ($traceId === null) {
            $traceId = Str::uuid();
        }

        return $traceId;
    }

    public static function renderExpectedValues(array $values): string
    {
        $result = '';

        foreach ($values as $value) {
            $result .= "\"{$value}\", ";
        }

        return \rtrim($result);
    }

    public static function setInstance(AbstractOutputMode $outputMode): void
    {
        self::$outputMode = $outputMode;
    }

    public static function getInstance(): AbstractOutputMode
    {
        $result = self::$outputMode;

        if ($result === null) {
            throw new Exception('Output mode is not defined');
        }

        return $result;
    }
}
