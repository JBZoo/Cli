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

/**
 * Class Icons
 * @package JBZoo\Cli
 */
class Icons
{
    public const GROUP_PROGRESS = 'progress';
    public const GROUP_FINISH   = 'finish';

    /**
     * @var array
     */
    private static array $icons = [
        self::GROUP_PROGRESS => [
            "\xF0\x9F\x8D\xBA", // :beer:
            "\xF0\x9F\x8D\xB7", // :wine:
            "\xF0\x9F\x8D\xB5", // :tea:
            "\xF0\x9F\x8D\xB8", // :cocktail:
            "\xF0\x9F\x8D\xB9", // :tropical_drink:
            "\xF0\x9F\x8D\xBB", // :beers:
            "\xF0\x9F\x8D\xBC", // :baby_bottle:
            "\xF0\x9F\x8D\xBE", // BOTTLE WITH POPPING CORK
            "\xF0\x9F\x8D\xBF", // POPCORN
            "\xF0\x9F\x92\xA4", // :zzz:
            "\xF0\x9F\x94\x9C", // :soon:
            "\xF0\x9F\x8D\xAE", // :custard:
            "\xF0\x9F\x8F\x84", // :surfer:
            "\xF0\x9F\x90\x8C", // :snail:
            "\xF0\x9F\x91\x89", // :point_right:
            "\xF0\x9F\x94\x80", // :twisted_rightwards_arrows:
            "\xF0\x9F\x94\xA5", // :fire:
            "\xF0\x9F\x97\xAF", // RIGHT ANGER BUBBLE
            "\xF0\x9F\x8C\x80", // :cyclone:
            "\xF0\x9F\x8C\x8A", // :ocean:
            "\xF0\x9F\x98\x88", // :smiling_imp:
            "\xF0\x9F\x98\x8B", // :yum:
            "\xF0\x9F\x98\x8A", // :blush:
            "\xF0\x9F\x98\x8E", // :sunglasses:
            "\xF0\x9F\x98\x93", // :sweat:
            "\xF0\x9F\x98\x90", // :neutral_face:
            "\xF0\x9F\x98\x91", // :expressionless:
            "\xF0\x9F\x98\xA0", // :angry:
            "\xF0\x9F\x98\x95", // :confused:
            "\xF0\x9F\x98\x96", // :confounded:
            "\xF0\x9F\x98\x9C", // :stuck_out_tongue_winking_eye:
            "\xF0\x9F\x98\x9D", // :stuck_out_tongue_closed_eyes:
            "\xF0\x9F\x98\xAC", // :grimacing:
            "\xF0\x9F\x98\xAD", // :sob:
            "\xF0\x9F\x98\xAA", // :sleepy:
            "\xF0\x9F\x98\xB5", // :dizzy_face:
            "\xF0\x9F\x98\xB3", // :flushed:
            "\xF0\x9F\x98\xB1", // :scream:
            "\xF0\x9F\x98\xB0", // :cold_sweat:
            "\xF0\x9F\x99\x80", // :scream_cat:
            "\xF0\x9F\x99\x88", // :see_no_evil:
            "\xF0\x9F\x98\xBC", // :smirk_cat:
            "\xF0\x9F\x98\xB9", // :joy_cat:
            "\xF0\x9F\x98\x8F", // :smirk:
            "\xF0\x9F\x98\x9F", // :worried:
            "\xF0\x9F\x98\xAF", // :hushed:
            "\xF0\x9F\x98\xB6", // :no_mouth:
            "\xF0\x9F\x98\xB4", // :sleeping:
            "\xF0\x9F\x98\xA1", // :rage:
            "\xF0\x9F\x98\x86", // :laughing:
            "\xF0\x9F\x98\x87", // :innocent:
            "\xF0\x9F\x98\x89", // :wink:
            "\xF0\x9F\x98\x8C", // :relieved:
        ],

        self::GROUP_FINISH => [
            "\xF0\x9F\x8E\x89", // :tada:
            "\xF0\x9F\x8E\x96", // MILITARY MEDAL
            "\xF0\x9F\x8E\x97", // REMINDER RIBBON
            "\xF0\x9F\x8E\xAC", // :clapper:
            "\xF0\x9F\x8E\xB2", // :game_die:
            "\xF0\x9F\x8F\x81", // :checkered_flag:
            "\xF0\x9F\x8F\x96", // BEACH WITH UMBRELLA
            "\xF0\x9F\x91\x80", // :eyes:
            "\xF0\x9F\x91\x8C", // :ok_hand:
            "\xF0\x9F\x91\x8D", // :thumbsup:
            "\xF0\x9F\x91\xBB", // :ghost:
            "\xF0\x9F\x92\x83", // :dancer:
            "\xF0\x9F\x92\xA5", // :boom:
            "\xF0\x9F\x94\x9A", // :end:
            "\xF0\x9F\x95\x8A", // DOVE OF PEACE
            "\xF0\x9F\x96\x96", // RAISED HAND WITH PART BETWEEN MIDDLE AND RING FINGERS
            "\xF0\x9F\x8E\xB1", // :8ball:
            "\xF0\x9F\x8E\xB3", // :bowling:
            "\xF0\x9F\x8F\x85", // SPORTS MEDAL
            "\xF0\x9F\x8F\x86", // :trophy:
            "\xF0\x9F\x91\x8A", // :punch:
            "\xF0\x9F\x92\xAA", // :muscle:
            "\xF0\x9F\x92\xAF", // :100:
            "\xF0\x9F\x92\xB0", // :moneybag:
            "\xF0\x9F\x97\xAF", // RIGHT ANGER BUBBLE
            "\xF0\x9F\x98\x88", // :smiling_imp:
            "\xF0\x9F\x98\x8B", // :yum:
            "\xF0\x9F\x98\x8A", // :blush:
            "\xF0\x9F\x98\x8E", // :sunglasses:
            "\xF0\x9F\x98\x93", // :sweat:
            "\xF0\x9F\x98\x92", // :unamused:
            "\xF0\x9F\x98\x90", // :neutral_face:
            "\xF0\x9F\x98\x91", // :expressionless:
            "\xF0\x9F\x98\xA0", // :angry:
            "\xF0\x9F\x98\x95", // :confused:
            "\xF0\x9F\x98\x96", // :confounded:
            "\xF0\x9F\x98\x9C", // :stuck_out_tongue_winking_eye:
            "\xF0\x9F\x98\x9D", // :stuck_out_tongue_closed_eyes:
            "\xF0\x9F\x98\xAC", // :grimacing:
            "\xF0\x9F\x98\xAD", // :sob:
            "\xF0\x9F\x98\xAA", // :sleepy:
            "\xF0\x9F\x98\xB5", // :dizzy_face:
            "\xF0\x9F\x98\xB3", // :flushed:
            "\xF0\x9F\x98\xB1", // :scream:
            "\xF0\x9F\x98\xB0", // :cold_sweat:
            "\xF0\x9F\x99\x80", // :scream_cat:
            "\xF0\x9F\x99\x88", // :see_no_evil:
            "\xF0\x9F\x98\xBC", // :smirk_cat:
            "\xF0\x9F\x98\xB9", // :joy_cat:
            "\xF0\x9F\x98\x8F", // :smirk:
            "\xF0\x9F\x98\x9F", // :worried:
            "\xF0\x9F\x98\xAF", // :hushed:
            "\xF0\x9F\x98\xB6", // :no_mouth:
            "\xF0\x9F\x98\xB4", // :sleeping:
            "\xF0\x9F\x98\xA1", // :rage:
            "\xF0\x9F\x98\x86", // :laughing:
            "\xF0\x9F\x98\x87", // :innocent:
            "\xF0\x9F\x98\x89", // :wink:
            "\xF0\x9F\x98\x8C", // :relieved:
        ]
    ];

    /**
     * @param string $group
     * @param bool   $isDecorated
     * @return string
     */
    public static function getRandomIcon(string $group, bool $isDecorated): string
    {
        if (!$isDecorated) {
            return $group === 'progress' ? '>' : '';
        }

        $icons = self::$icons[$group];
        \shuffle($icons);

        return $icons[0];
    }
}
