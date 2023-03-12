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

class Codes
{
    // General codes
    public const OK                       = 0;
    public const GENERAL_ERROR            = 1;
    public const MISUSE_OF_SHELL_BUILTINS = 2;

    // Error types
    public const INVOKED_COMMAND_CANNOT_EXECUTE = 126;
    public const COMMAND_NOT_FOUND              = 127;
    public const INVALID_EXIT_ARGUMENT          = 128;

    // Signals
    public const HANGUP                                         = 129;
    public const INTERRUPT                                      = 130;
    public const QUIT_AND_DUMP_CORE                             = 131;
    public const ILLEGAL_INSTRUCTION                            = 132;
    public const TRACE_TRAP                                     = 133;
    public const BREAKPOINT_TRAP                                = 133;
    public const PROCESS_ABORTED                                = 134;
    public const ACCESS_TO_UNDEFINED_PORTION_OF_MEMORY_OBJECT   = 135;
    public const FLOATING_POINT_EXCEPTION                       = 136;
    public const ERRONEOUS_ARITHMETIC_OPERATION                 = 136;
    public const KILLED                                         = 137;
    public const TERMINATE_IMMEDIATELY                          = 137;
    public const SEGMENTATION_VIOLATION                         = 139;
    public const WRITE_TO_PIPE_WITH_NO_ONE_READING              = 141;
    public const SIGNAL_RAISED_BY_ALARM                         = 142;
    public const TERMINATION                                    = 143;
    public const CHILD_PROCESS_TERMINATED                       = 145;
    public const CONTINUE_IF_STOPPED                            = 146;
    public const STOP_EXECUTING_TEMPORARILY                     = 147;
    public const TERMINAL_STOP_SIGNAL                           = 148;
    public const BACKGROUND_PROCESS_ATTEMPTING_TO_READ_FROM_TTY = 149;
    public const BACKGROUND_PROCESS_ATTEMPTING_TO_WRITE_TO_TTY  = 150;
    public const URGENT_DATA_AVAILABLE_ON_SOCKET                = 151;
    public const CPU_TIME_LIMIT_EXCEEDED                        = 152;
    public const FILE_SIZE_LIMIT_EXCEEDED                       = 153;
    public const SIGNAL_RAISED_BY_TIMER_COUNTING_VIRTUAL_TIME   = 154;
    public const VIRTUAL_TIMER_EXPIRED                          = 154;
    public const PROFILING_TIMER_EXPIRED                        = 155;
    public const POLLABLE_EVENT                                 = 157;
    public const BAD_SYSCALL                                    = 159;

    // See also: ./symfony/process/Process.php
}
