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

namespace JBZoo\PHPUnit;

use function JBZoo\Data\json;

/**
 * Class CliOptionsTest
 * @package JBZoo\PHPUnit
 */
class CliOptionsTest extends PHPUnit
{
    public function testOptionNone()
    {
        $option = 'opt-none';
        $output = json(CliTestHelper::executeReal('CliOptionsTest'))->getArrayCopy();
        isSame([
            "Default" => false,
            "Bool"    => false,
            "Int"     => 0,
            "Float"   => 0,
            "String"  => "",
            "Array"   => [false]
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}"))->getArrayCopy();
        isSame([
            "Default" => true,
            "Bool"    => true,
            "Int"     => 1,
            "Float"   => 1,
            "String"  => "1",
            "Array"   => [true]
        ], $output[$option]);
    }

    public function testOptionRequired()
    {
        $option = 'opt-req';
        $output = json(CliTestHelper::executeReal('CliOptionsTest'))->getArrayCopy();
        isSame([
            "Default" => null,
            "Bool"    => false,
            "Int"     => 0,
            "Float"   => 0,
            "String"  => "",
            "Array"   => []
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}='123.5'"))->getArrayCopy();
        isSame([
            "Default" => '123.5',
            "Bool"    => true,
            "Int"     => 123,
            "Float"   => 123.5,
            "String"  => "123.5",
            "Array"   => ['123.5']
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}='false'"))->getArrayCopy();
        isSame([
            "Default" => 'false',
            "Bool"    => false,
            "Int"     => 0,
            "Float"   => 0,
            "String"  => "false",
            "Array"   => ['false']
        ], $output[$option]);
    }

    public function testOptionRequiredAndDefault()
    {
        $option = 'opt-req-default';
        $output = json(CliTestHelper::executeReal('CliOptionsTest'))->getArrayCopy();
        isSame([
            "Default" => '456.8',
            "Bool"    => true,
            "Int"     => 456,
            "Float"   => 456.8,
            "String"  => "456.8",
            "Array"   => ['456.8']
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}='123.5'"))->getArrayCopy();
        isSame([
            "Default" => '123.5',
            "Bool"    => true,
            "Int"     => 123,
            "Float"   => 123.5,
            "String"  => "123.5",
            "Array"   => ['123.5']
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}='0'"))->getArrayCopy();
        isSame([
            "Default" => '0',
            "Bool"    => false,
            "Int"     => 0,
            "Float"   => 0,
            "String"  => "0",
            "Array"   => ['0']
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}='false'"))->getArrayCopy();
        isSame([
            "Default" => 'false',
            "Bool"    => false,
            "Int"     => 0,
            "Float"   => 0,
            "String"  => "false",
            "Array"   => ['false']
        ], $output[$option]);
    }

    public function testOptionOptional()
    {
        $option = 'opt-optional';
        $output = json(CliTestHelper::executeReal('CliOptionsTest'))->getArrayCopy();
        isSame([
            "Default" => null,
            "Bool"    => false,
            "Int"     => 0,
            "Float"   => 0,
            "String"  => "",
            "Array"   => []
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}='123.5'"))->getArrayCopy();
        isSame([
            'Default' => '123.5',
            'Bool'    => true,
            'Int'     => 123,
            'Float'   => 123.5,
            'String'  => '123.5',
            'Array'   => ['123.5'],
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}='0'"))->getArrayCopy();
        isSame([
            'Default' => '0',
            'Bool'    => false,
            'Int'     => 0,
            'Float'   => 0,
            'String'  => '0',
            'Array'   => ['0'],
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}='false'"))->getArrayCopy();
        isSame([
            'Default' => 'false',
            'Bool'    => false,
            'Int'     => 0,
            'Float'   => 0,
            'String'  => 'false',
            'Array'   => ['false'],
        ], $output[$option]);
    }

    public function testOptionOptionalDefault()
    {
        $option = 'opt-optional-default';
        $output = json(CliTestHelper::executeReal('CliOptionsTest'))->getArrayCopy();
        isSame([
            "Default" => '456.8',
            "Bool"    => true,
            "Int"     => 456,
            "Float"   => 456.8,
            "String"  => "456.8",
            "Array"   => ['456.8']
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}='123.5'"))->getArrayCopy();
        isSame([
            'Default' => '123.5',
            'Bool'    => true,
            'Int'     => 123,
            'Float'   => 123.5,
            'String'  => '123.5',
            'Array'   => ['123.5'],
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}='0'"))->getArrayCopy();
        isSame([
            'Default' => '0',
            'Bool'    => false,
            'Int'     => 0,
            'Float'   => 0,
            'String'  => '0',
            'Array'   => ['0'],
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}='false'"))->getArrayCopy();
        isSame([
            'Default' => 'false',
            'Bool'    => false,
            'Int'     => 0,
            'Float'   => 0,
            'String'  => 'false',
            'Array'   => ['false'],
        ], $output[$option]);
    }

    public function testOptionArrayOptional()
    {
        $option = 'opt-array-optional';
        $output = json(CliTestHelper::executeReal('CliOptionsTest'))->getArrayCopy();
        isSame([
            "Default" => [],
            "Bool"    => false,
            "Int"     => 0,
            "Float"   => 0,
            "String"  => "",
            "Array"   => []
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}='123.5' --{$option}='789.1'"))->getArrayCopy();
        isSame([
            'Default' => ['123.5', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['123.5', '789.1'],
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}=0 --{$option}='789.1'"))->getArrayCopy();
        isSame([
            'Default' => ['0', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['0', '789.1'],
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}='false' --{$option}='789.1'"))->getArrayCopy();
        isSame([
            'Default' => ['false', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['false', '789.1'],
        ], $output[$option]);
    }

    public function testOptionArrayRequired()
    {
        $option = 'opt-array-req';
        $output = json(CliTestHelper::executeReal('CliOptionsTest'))->getArrayCopy();
        isSame([
            "Default" => [],
            "Bool"    => false,
            "Int"     => 0,
            "Float"   => 0,
            "String"  => "",
            "Array"   => []
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}='123.5' --{$option}='789.1'"))->getArrayCopy();
        isSame([
            'Default' => ['123.5', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['123.5', '789.1'],
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}=0 --{$option}='789.1'"))->getArrayCopy();
        isSame([
            'Default' => ['0', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['0', '789.1'],
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}='false' --{$option}='789.1'"))->getArrayCopy();
        isSame([
            'Default' => ['false', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['false', '789.1'],
        ], $output[$option]);
    }

    public function testOptionArrayRequiredDefault()
    {
        $option = 'opt-array-req-default';
        $output = json(CliTestHelper::executeReal('CliOptionsTest'))->getArrayCopy();
        isSame([
            "Default" => ['456.8'],
            "Bool"    => true,
            "Int"     => 456,
            "Float"   => 456.8,
            "String"  => "456.8",
            "Array"   => ['456.8']
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}='123.5' --{$option}='789.1'"))->getArrayCopy();
        isSame([
            'Default' => ['123.5', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['123.5', '789.1'],
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}=0 --{$option}='789.1'"))->getArrayCopy();
        isSame([
            'Default' => ['0', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['0', '789.1'],
        ], $output[$option]);

        $output = json(CliTestHelper::executeReal("CliOptionsTest --{$option}='false' --{$option}='789.1'"))->getArrayCopy();
        isSame([
            'Default' => ['false', '789.1'],
            'Bool'    => true,
            'Int'     => 789,
            'Float'   => 789.1,
            'String'  => '789.1',
            'Array'   => ['false', '789.1'],
        ], $output[$option]);
    }
}
