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

namespace JBZoo\TestApp;

use JBZoo\Cli\CliApplication;

const PATH_ROOT = __DIR__;

$composerAutoloadFiles = array_values(array_filter([
    realpath(PATH_ROOT . '/vendor/autoload.php'),
    realpath(dirname(PATH_ROOT, 1) . '/vendor/autoload.php'),
    realpath(dirname(PATH_ROOT, 2) . '/vendor/autoload.php'),
    realpath(dirname(PATH_ROOT, 3) . '/vendor/autoload.php'),
    realpath(dirname(PATH_ROOT, 4) . '/vendor/autoload.php'),
    realpath(dirname(PATH_ROOT, 5) . '/vendor/autoload.php'),
    realpath(dirname(PATH_ROOT, 6) . '/vendor/autoload.php'),
]));

$composerAutoloadFile = $composerAutoloadFiles[0] ?? null;
if ($composerAutoloadFile) {
    require_once $composerAutoloadFile;
} else {
    throw new \RuntimeException("Composer autoload file not found");
}


$application = new CliApplication('Dummy App', '@git-version@');
$application->registerCommandsByPath(__DIR__ . '/Commands', __NAMESPACE__);
$application->run();
