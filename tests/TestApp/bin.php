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

namespace JBZoo\PHPUnit\TestApp;

use JBZoo\Cli\CliApplication;

if (!\defined('JBZOO_PATH_ROOT')) {
    \define('JBZOO_PATH_ROOT', __DIR__);
}

if (!\defined('JBZOO_PATH_BIN')) {
    \define('JBZOO_PATH_BIN', JBZOO_PATH_ROOT . '/' . \pathinfo(__FILE__, \PATHINFO_BASENAME));
}

require '../../vendor/autoload.php';

$application = new CliApplication('Dummy App', '@git-version@');
$application->registerCommandsByPath(JBZOO_PATH_ROOT . '/Commands', __NAMESPACE__);
$application->run();
