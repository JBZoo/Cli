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

namespace ExampleApp\Commands;

use JBZoo\Cli\CliCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ExampleAgruments
 */
class ExampleAgruments extends CliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('example:agruments')
            ->setDescription('Show description of command.')
            ->setHelp(
                "Full description and usage of command.\n" .
                "You can use severla lines."
            )

            // None
            ->addOption('opt', 'o', InputOption::VALUE_NONE, 'Just a boolean flag')

            // Required
            ->addOption('opt-req', null, InputOption::VALUE_REQUIRED, 'The option with required value')
            ->addOption('opt-req-default', null, InputOption::VALUE_REQUIRED,
                'The option is requred but it has default value', 42)

            // Optional
            ->addOption('opt-optional', null, InputOption::VALUE_OPTIONAL,
                'Option is not required and can be undefined')
            ->addOption('opt-optional-default', null, InputOption::VALUE_OPTIONAL,
                'Option is not required with default value', 42)

            // Array
            ->addOption(
                'opt-array-optional',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                "Multiple values are allowed. Can be empty"
            )
            ->addOption(
                'opt-array-req',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Multiple values are allowed. Value is required'
            )
            ->addOption(
                'opt-array-req-default',
                'a',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Multiple values are allowed. Value is required with defaut value',
                [42, 'foo', 'bar']
            )

            // Arguments
            ->addArgument('arg-req', InputOption::VALUE_REQUIRED, 'Argument is required')
            ->addArgument('arg-default', InputOption::VALUE_REQUIRED, 'Argument is optional with default value', 42)
            ->addArgument('arg-optional', InputOption::VALUE_OPTIONAL, 'Argument is optional, no default value');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeAction(): int
    {
        ////////////////////////////////////////// Just a boolean flag
        // ./my-app example:agruments
        $this->getOpt('opt'); // false

        // ./my-app example:agruments --opt
        $this->getOpt('opt'); // true

        // ./my-app example:agruments -o
        $this->getOpt('opt'); // true


        ////////////////////////////////////////// The option requires a value
        // ./my-app example:agruments --opt-req
        $this->getOpt('opt-req'); // Exception: The "--opt-req" option requires a value.

        // ./my-app example:agruments --opt-req=123.6
        $this->getOpt('opt-req'); // "123.6"

        // ./my-app example:agruments --opt-req=123.6
        $this->getOptBool('opt-req'); // true

        // ./my-app example:agruments --opt-req=123.6
        $this->getOptInt('opt-req'); // 123

        // ./my-app example:agruments --opt-req=123.6
        $this->getOptFloat('opt-req'); // 123.6

        // ./my-app example:agruments --opt-req="    123.6   "
        $this->getOptString('opt-req'); // "123.6"

        // ./my-app example:agruments --opt-req=123.6
        $this->getOptArray('opt-req'); // ["123.6"]

        // ./my-app example:agruments --opt-req="15 July 2021 13:48:00"
        $this->getOptDatetime('opt-req'); // \DateTimeImmutable {date: 2021-07-15 13:48:00. UTC (+00:00) }

        ////////////////////////////////////////// The option requires a value with default value
        // ./my-app example:agruments
        $this->getOpt('opt-req-default'); // 42

        // ./my-app example:agruments --opt-req-default
        $this->getOpt('opt-req-default'); // Exception: The "--opt-req-default" option requires a value.

        // ./my-app example:agruments --opt-req-default=123.6
        $this->getOpt('opt-req-default'); // "123.6"


        ////////////////////////////////////////// Multiple values are allowed. Value is required with defaut value
        // ./my-app example:agruments
        $this->getOpt('opt-array-req-default'); // "bar"

        // ./my-app example:agruments
        $this->getOptArray('opt-array-req-default'); // [42, 'foo', 'bar']

        // ./my-app example:agruments --opt-array-req-default=123 --opt-array-req-default=asdasd
        $this->getOptArray('opt-array-req-default'); // ['123', 'Qwerty']

        // ./my-app example:agruments -a123 -aQwerty
        $this->getOptArray('opt-array-req-default'); // ['123', 'Qwerty']

        // ./my-app example:agruments -a123 -aQwerty
        $this->getOpt('opt-array-req-default'); // 'Qwerty'

        // ./my-app example:agruments -aQwerty -aAsd
        $this->getOpt('opt-array-req-default'); // 'Asd'


        ////////////////////////////////////////// Arguments
        // ./my-app example:agruments
        $this->input->getArgument('arg-req'); // null

        // ./my-app example:agruments Qwerty
        $this->input->getArgument('arg-req'); // "Qwerty"

        // ./my-app example:agruments Qwerty
        $this->input->getArgument('arg-default'); // 42

        // ./my-app example:agruments Qwerty Some text
        $this->input->getArgument('arg-default'); // "Some"

        // ./my-app example:agruments Qwerty "Some text"
        $this->input->getArgument('arg-default'); // "Some text"

        // ./my-app example:agruments Qwerty "Some text"
        $this->input->getArgument('arg-optional'); // []

        // ./my-app example:agruments Qwerty "Some text" 123
        $this->input->getArgument('arg-optional'); // ["123"]

        // ./my-app example:agruments Qwerty "Some text" 123 456 "789 098"
        $this->input->getArgument('arg-optional'); // ["123", "456", "789 098"]


        ////////////////////////////////////////// Standard input
        // echo " Qwerty 123 " | php ./my-app example:agruments
        self::getStdIn(); // " Qwerty 123 \n"

        return 0;
    }
}
