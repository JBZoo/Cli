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

use Monolog\Level;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

class OutLvl
{
    public const Q       = 'q';
    public const DEFAULT = '';
    public const V       = 'v';
    public const VV      = 'vv';
    public const VVV     = 'vvv';
    public const E       = 'e';

    public const DEBUG     = 'debug';
    public const INFO      = 'info';
    public const WARNING   = 'warning';
    public const ERROR     = 'error';
    public const EXCEPTION = 'exception';
    public const LEGACY    = 'legacy';

    public static function mapToPsrLevel(int|string $level): string
    {
        $map = [
            // -vvv
            OutputInterface::VERBOSITY_DEBUG => LogLevel::DEBUG,
            self::DEBUG                      => LogLevel::DEBUG,
            self::VVV                        => LogLevel::DEBUG,

            // -vv OR -v
            OutputInterface::VERBOSITY_VERY_VERBOSE => LogLevel::INFO,
            OutputInterface::VERBOSITY_VERBOSE      => LogLevel::INFO,
            self::INFO                              => LogLevel::INFO,
            self::VV                                => LogLevel::INFO,
            self::V                                 => LogLevel::INFO,
            self::Q                                 => LogLevel::INFO,

            // Regular
            OutputInterface::VERBOSITY_NORMAL => LogLevel::NOTICE,
            self::DEFAULT                     => LogLevel::NOTICE,

            // Different level of issues
            self::WARNING   => LogLevel::WARNING,
            self::LEGACY    => LogLevel::WARNING,
            self::E         => LogLevel::ERROR,
            self::ERROR     => LogLevel::ERROR,
            self::EXCEPTION => LogLevel::CRITICAL,
        ];

        return $map[$level] ?? LogLevel::INFO;
    }

    public static function isPsrErrorLevel(string $psrLevel): bool
    {
        return \in_array($psrLevel, [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
        ], true);
    }
}
