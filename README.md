# JBZoo / Cli

[![CI](https://github.com/JBZoo/Cli/actions/workflows/main.yml/badge.svg?branch=master)](https://github.com/JBZoo/Cli/actions/workflows/main.yml?query=branch%3Amaster)    [![codecov](https://codecov.io/gh/JBZoo/Cli/branch/master/graph/badge.svg)](https://codecov.io/gh/JBZoo/Cli/branch/master)    [![Psalm Coverage](https://shepherd.dev/github/JBZoo/Cli/coverage.svg)](https://shepherd.dev/github/JBZoo/Cli)    [![CodeFactor](https://www.codefactor.io/repository/github/jbzoo/cli/badge)](https://www.codefactor.io/repository/github/jbzoo/cli/issues)    [![PHP Strict Types](https://img.shields.io/badge/strict__types-%3D1-brightgreen)](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.strict)    
[![Stable Version](https://poser.pugx.org/jbzoo/cli/version)](https://packagist.org/packages/jbzoo/cli)    [![Total Downloads](https://poser.pugx.org/jbzoo/cli/downloads)](https://packagist.org/packages/jbzoo/cli/stats)    [![Dependents](https://poser.pugx.org/jbzoo/cli/dependents)](https://packagist.org/packages/jbzoo/cli/dependents?order_by=downloads)    [![Visitors](https://visitor-badge.glitch.me/badge?page_id=jbzoo.cli)]()    [![GitHub License](https://img.shields.io/github/license/jbzoo/cli)](https://github.com/JBZoo/Cli/blob/master/LICENSE)

## Installing

```sh
composer require jbzoo/cli
```

https://symfony.com/doc/current/components/console.html

## Usage example

### The minimal reference example

Binary file:

```php
#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace ExampleApp;

use JBZoo\Cli\CliApplication;

require_once __DIR__ . '/vendor/autoload.php';

$application = new CliApplication('Your Application Name', 'v1.0.0');
$application->registerCommandsByPath(__DIR__ . '/Commands', __NAMESPACE__); // Sran directory to filen commands
$application->run();

```

The simplest CLI action
```php
<?php

declare(strict_types=1);

namespace ExampleApp/Commands;

use JBZoo\Cli\CliCommand;

class MyCommand extends CliCommand
{
    protected function configure(): void
    {
        $this->setName('my-action');
        parent::configure();
    }

    protected function executeAction(): int
    {
        $this->_('Hello world!');
        return 0;
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
