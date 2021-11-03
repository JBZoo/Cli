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

use JBZoo\Cli\CliCommand;
use Symfony\Component\Console\Input\InputOption;

use function JBZoo\Data\json;

/**
 *
 */
class TestCliOptions extends CliCommand
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $defaultValue = '456.8';

        $this
            ->setName('test:cli-options')
            // None
            ->addOption('opt-none', null, InputOption::VALUE_NONE, '')
            // Required
            ->addOption('opt-req', null, InputOption::VALUE_REQUIRED, '')
            ->addOption('opt-req-default', null, InputOption::VALUE_REQUIRED, '', $defaultValue)
            // Optional
            ->addOption('opt-optional', null, InputOption::VALUE_OPTIONAL, '')
            ->addOption('opt-optional-default', null, InputOption::VALUE_OPTIONAL, '', $defaultValue)
            // Array
            ->addOption('opt-array-optional', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '')
            ->addOption('opt-array-req', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, '')
            ->addOption('opt-array-req-default', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, '',
                [$defaultValue]);

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function executeAction(): int
    {
        $options = [
            'opt-none',
            'opt-req',
            'opt-req-default',
            'opt-optional',
            'opt-optional-default',
            'opt-array-optional',
            'opt-array-req',
            'opt-array-req-default',
        ];

        foreach ($options as $option) {
            $result[$option] = [
                'Default' => $this->input->getOption($option),
                'Bool'    => $this->getOptBool($option),
                'Int'     => $this->getOptInt($option),
                'Float'   => $this->getOptFloat($option),
                'String'  => $this->getOptString($option),
                'Array'   => $this->getOptArray($option),
            ];
        }

        $this->_((string)json($result));

        return 0;
    }
}
