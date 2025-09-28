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

use JBZoo\Utils\Str;

final class CliRender
{
    public static function list(array $metrics, ?string $addDot = null): string
    {
        $result = Str::listToDescription($metrics, true);

        if ($result === null) {
            return '';
        }

        if ($addDot !== null) {
            $addDot = \trim($addDot);
            $list   = \explode("\n", $result);

            \array_walk($list, static function (string &$item) use ($addDot): void {
                $item = \trim($item);
                if ($item !== '') {
                    $item = " {$addDot} {$item}";
                }
            });

            $result = \implode("\n", $list);
        }

        return $result;
    }
}
