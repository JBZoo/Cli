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

use JBZoo\PHPUnit\CovCatcher;
use JBZoo\Utils\Sys;

\define('PROJECT_ROOT', \dirname(__DIR__, 2));

// This test entry point runs a CLI subprocess whose exact output the tests assert. On PHP 8.5 some
// older transitive dev-tool deps (amphp / guzzle-promises / sabre-event via php-coveralls, surfaced
// under --prefer-lowest) emit "implicitly nullable" E_DEPRECATED at load time that would otherwise
// pollute that captured output. Silence deprecations for this test harness only; the app's own code
// is checked for deprecated APIs by phan/psalm/phpstan.
\error_reporting(\error_reporting() & ~\E_DEPRECATED);

require_once PROJECT_ROOT . '/vendor/autoload.php';

$cliIndexFile = PROJECT_ROOT . '/tests/TestApp/bin.php';

\define('JBZOO_PATH_ROOT', __DIR__);
\define('JBZOO_PATH_BIN', JBZOO_PATH_ROOT . '/' . \pathinfo(__FILE__, \PATHINFO_BASENAME));

if (\class_exists(CovCatcher::class) && Sys::hasXdebug()) {
    $covCatcher = new CovCatcher(\uniqid('prefix-', true), [
        'html'      => 0,
        'xml'       => 1,
        'cov'       => 1,
        'src'       => PROJECT_ROOT . '/src',
        'build_xml' => PROJECT_ROOT . '/build/coverage_xml',
        'build_cov' => PROJECT_ROOT . '/build/coverage_cov',
    ]);

    $result = $covCatcher->includeFile($cliIndexFile);
} else {
    $result = require_once $cliIndexFile;
}

return $result;
