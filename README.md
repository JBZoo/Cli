# JBZoo / Cli

[![CI](https://github.com/JBZoo/Cli/actions/workflows/main.yml/badge.svg?branch=master)](https://github.com/JBZoo/Cli/actions/workflows/main.yml?query=branch%3Amaster)    [![codecov](https://codecov.io/gh/JBZoo/Cli/branch/master/graph/badge.svg)](https://codecov.io/gh/JBZoo/Cli/branch/master)    [![Psalm Coverage](https://shepherd.dev/github/JBZoo/Cli/coverage.svg)](https://shepherd.dev/github/JBZoo/Cli)    [![CodeFactor](https://www.codefactor.io/repository/github/jbzoo/cli/badge)](https://www.codefactor.io/repository/github/jbzoo/cli/issues)    [![PHP Strict Types](https://img.shields.io/badge/strict__types-%3D1-brightgreen)](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.strict)    
[![Stable Version](https://poser.pugx.org/jbzoo/cli/version)](https://packagist.org/packages/jbzoo/cli)    [![Total Downloads](https://poser.pugx.org/jbzoo/cli/downloads)](https://packagist.org/packages/jbzoo/cli/stats)    [![Dependents](https://poser.pugx.org/jbzoo/cli/dependents)](https://packagist.org/packages/jbzoo/cli/dependents?order_by=downloads)    [![Visitors](https://visitor-badge.glitch.me/badge?page_id=jbzoo.cli)]()    [![GitHub License](https://img.shields.io/github/license/jbzoo/cli)](https://github.com/JBZoo/Cli/blob/master/LICENSE)

## Installing

```sh
composer require jbzoo/cli
```

https://symfony.com/doc/current/components/console.html

## Usage example

The simplest CLI application has the following file structure. See the [Demo App](demo) for more details.

```
/path/to/app/
    my-app                      # Binrary file (See below)
    composer.json               # Composer file
    /Commands/                  # Commands directory
        Simple.php              # One of the commands (See below)
    /vendor/
        autoload.php            # Composer autoload
```

[./composer.json`](demo/composer.json)

```json
{
    "name"        : "vendor/cli-application",
    "type"        : "project",
    "description" : "Example of CLI App based on JBZoo/CLI",
    "license"     : "MIT",
    "keywords"    : ["cli", "application", "example"],

    "require"     : {
        "php"       : ">=7.2",
        "jbzoo/cli" : "^1.0.0"
    },

    "require-dev" : {
        "roave/security-advisories" : "dev-latest"
    },

    "autoload"    : {
        "psr-4" : {"DemoApp\\" : ""}
    },

    "bin"         : ["my-app"]
}
```

Binary file: [demo/my-app](demo/my-app)

```php
#!/usr/bin/env php
<?php declare(strict_types=1);

namespace ExampleApp;

use JBZoo\Cli\CliApplication;

require_once __DIR__ . '/vendor/autoload.php';

// Set your application name and version.
$application = new CliApplication('Your Application Name', 'v1.0.0');

// Scan directory to find commands.
//  * It doesn't work recursively!
//  * They must be inherited from the class \JBZoo\Cli\CliCommand
$application->registerCommandsByPath(__DIR__ . '/Commands', __NAMESPACE__);

// Execute it.
$application->run();

```

The simplest CLI action: [./Commands/Simple.php](demo/Commands/Simple.php)

```php
<?php declare(strict_types=1);

namespace DemoApp\Commands;

use JBZoo\Cli\CliCommand;
use JBZoo\Cli\Codes;

class Simple extends CliCommand
{
    protected function configure(): void
    {
        // Action name. It will be used in command line.
        // Example: `./my-app simple`
        $this->setName('simple');

        // Defined inhereted CLI options. See ./src/CliCommand.php for details.
        parent::configure();
    }

    protected function executeAction(): int
    {
        // Your code here
        $this->_('Hello world!');

        // Exit code. 0 - success, 1 - error.
        return Codes::OK;
    }
}
```

### See also

* https://github.com/kevinlebrun/colors.php
* https://packagist.org/packages/php-school/cli-menu
* https://github.com/nunomaduro/collision
* https://packagist.org/packages/splitbrain/php-cli
* https://github.com/php-school/terminal
* https://github.com/hoaproject/Console
* https://github.com/thephpleague/climate
* https://tldp.org/LDP/abs/html/exitcodes.html

## Unit tests and check code style

```sh
make update
make test-all
```

### License

MIT
