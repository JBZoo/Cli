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

use JBZoo\Utils\Str;

/**
 * Class CliRender
 * @package JBZoo\Cli
 */
class CliRender
{
    /**
     * @param string[]    $metrics
     * @param string|null $addDot
     * @return string
     */
    public static function list(array $metrics, ?string $addDot = null): string
    {
        $result = Str::listToDescription($metrics, true);
        if (!$result) {
            return '';
        }

        if (null !== $addDot) {
            $addDot = trim($addDot);
            $list = explode("\n", $result);
            array_walk($list, function (string &$item) use ($addDot) {
                if (trim($item)) {
                    $item = " {$addDot} {$item}";
                }
            });

            $result = implode("\n", $list);
        }

        return $result;
    }
}
