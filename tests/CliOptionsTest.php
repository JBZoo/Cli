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

namespace JBZoo\PHPUnit;

class CliOptionsTest extends PHPUnit
{
    public function testOptionNone(): void
    {
        $option = 'opt-none';

        $cmdResult = Helper::executeReal('test:cli-options');
        isSame([
            'Default' => false,
            'Bool'    => false,
            'Int'     => 0,
            'Float'   => 0,
            'String'  => '',
            'Array'   => [false],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}");
        isSame([
            'Default' => true,
            'Bool'    => true,
            'Int'     => 1,
            'Float'   => 1,
            'String'  => '1',
            'Array'   => [true],
        ], $cmdResult->stdJson()->get($option));
    }

    public function testOptionRequired(): void
    {
        $option    = 'opt-req';
        $cmdResult = Helper::executeReal('test:cli-options');
        isSame([
            'Default' => null,
            'Bool'    => false,
            'Int'     => 0,
            'Float'   => 0,
            'String'  => '',
            'Array'   => [],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}='123.5'");
        isSame([
            'Default' => '123.5',
            'Bool'    => true,
            'Int'     => 123,
            'Float'   => 123.5,
            'String'  => '123.5',
            'Array'   => ['123.5'],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}='false'");
        isSame([
            'Default' => 'false',
            'Bool'    => false,
            'Int'     => 0,
            'Float'   => 0,
            'String'  => 'false',
            'Array'   => ['false'],
        ], $cmdResult->stdJson()->get($option));
    }

    public function testOptionRequiredAndDefault(): void
    {
        $option    = 'opt-req-default';
        $cmdResult = Helper::executeReal('test:cli-options');
        isSame([
            'Default' => '456.8',
            'Bool'    => true,
            'Int'     => 456,
            'Float'   => 456.8,
            'String'  => '456.8',
            'Array'   => ['456.8'],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}='123.5'");
        isSame([
            'Default' => '123.5',
            'Bool'    => true,
            'Int'     => 123,
            'Float'   => 123.5,
            'String'  => '123.5',
            'Array'   => ['123.5'],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}='0'");
        isSame([
            'Default' => '0',
            'Bool'    => false,
            'Int'     => 0,
            'Float'   => 0,
            'String'  => '0',
            'Array'   => ['0'],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}='false'");
        isSame([
            'Default' => 'false',
            'Bool'    => false,
            'Int'     => 0,
            'Float'   => 0,
            'String'  => 'false',
            'Array'   => ['false'],
        ], $cmdResult->stdJson()->get($option));
    }

    public function testOptionOptional(): void
    {
        $option    = 'opt-optional';
        $cmdResult = Helper::executeReal('test:cli-options');
        isSame([
            'Default' => null,
            'Bool'    => false,
            'Int'     => 0,
            'Float'   => 0,
            'String'  => '',
            'Array'   => [],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}='123.5'");
        isSame([
            'Default' => '123.5',
            'Bool'    => true,
            'Int'     => 123,
            'Float'   => 123.5,
            'String'  => '123.5',
            'Array'   => ['123.5'],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}='0'");
        isSame([
            'Default' => '0',
            'Bool'    => false,
            'Int'     => 0,
            'Float'   => 0,
            'String'  => '0',
            'Array'   => ['0'],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}='false'");
        isSame([
            'Default' => 'false',
            'Bool'    => false,
            'Int'     => 0,
            'Float'   => 0,
            'String'  => 'false',
            'Array'   => ['false'],
        ], $cmdResult->stdJson()->get($option));
    }

    public function testOptionOptionalDefault(): void
    {
        $option    = 'opt-optional-default';
        $cmdResult = Helper::executeReal('test:cli-options');
        isSame([
            'Default' => '456.8',
            'Bool'    => true,
            'Int'     => 456,
            'Float'   => 456.8,
            'String'  => '456.8',
            'Array'   => ['456.8'],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}='123.5'");
        isSame([
            'Default' => '123.5',
            'Bool'    => true,
            'Int'     => 123,
            'Float'   => 123.5,
            'String'  => '123.5',
            'Array'   => ['123.5'],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}='0'");
        isSame([
            'Default' => '0',
            'Bool'    => false,
            'Int'     => 0,
            'Float'   => 0,
            'String'  => '0',
            'Array'   => ['0'],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}='false'");
        isSame([
            'Default' => 'false',
            'Bool'    => false,
            'Int'     => 0,
            'Float'   => 0,
            'String'  => 'false',
            'Array'   => ['false'],
        ], $cmdResult->stdJson()->get($option));
    }

    public function testOptionArrayOptional(): void
    {
        $option    = 'opt-array-optional';
        $cmdResult = Helper::executeReal('test:cli-options');
        isSame([
            'Default' => [],
            'Bool'    => false,
            'Int'     => 0,
            'Float'   => 0,
            'String'  => '',
            'Array'   => [],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}='123.5' --{$option}='789.1'");
        isSame([
            'Default' => ['123.5', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['123.5', '789.1'],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}=0 --{$option}='789.1'");
        isSame([
            'Default' => ['0', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['0', '789.1'],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}='false' --{$option}='789.1'");
        isSame([
            'Default' => ['false', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['false', '789.1'],
        ], $cmdResult->stdJson()->get($option));
    }

    public function testOptionArrayRequired(): void
    {
        $option    = 'opt-array-req';
        $cmdResult = Helper::executeReal('test:cli-options');
        isSame([
            'Default' => [],
            'Bool'    => false,
            'Int'     => 0,
            'Float'   => 0,
            'String'  => '',
            'Array'   => [],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}='123.5' --{$option}='789.1'");
        isSame([
            'Default' => ['123.5', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['123.5', '789.1'],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}=0 --{$option}='789.1'");
        isSame([
            'Default' => ['0', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['0', '789.1'],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}='false' --{$option}='789.1'");
        isSame([
            'Default' => ['false', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['false', '789.1'],
        ], $cmdResult->stdJson()->get($option));
    }

    public function testOptionArrayRequiredDefault(): void
    {
        $option    = 'opt-array-req-default';
        $cmdResult = Helper::executeReal('test:cli-options');
        isSame([
            'Default' => ['456.8'],
            'Bool'    => true,
            'Int'     => 456,
            'Float'   => 456.8,
            'String'  => '456.8',
            'Array'   => ['456.8'],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}='123.5' --{$option}='789.1'");
        isSame([
            'Default' => ['123.5', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['123.5', '789.1'],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}=0 --{$option}='789.1'");
        isSame([
            'Default' => ['0', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['0', '789.1'],
        ], $cmdResult->stdJson()->get($option));

        $cmdResult = Helper::executeReal("test:cli-options --{$option}='false' --{$option}='789.1'");
        isSame([
            'Default' => ['false', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['false', '789.1'],
        ], $cmdResult->stdJson()->get($option));
    }
}
