# JBZoo / Cli

[![CI](https://github.com/JBZoo/Cli/actions/workflows/main.yml/badge.svg?branch=master)](https://github.com/JBZoo/Cli/actions/workflows/main.yml?query=branch%3Amaster)    [![codecov](https://codecov.io/gh/JBZoo/Cli/branch/master/graph/badge.svg)](https://codecov.io/gh/JBZoo/Cli/branch/master)    [![Psalm Coverage](https://shepherd.dev/github/JBZoo/Cli/coverage.svg)](https://shepherd.dev/github/JBZoo/Cli)    [![CodeFactor](https://www.codefactor.io/repository/github/jbzoo/cli/badge)](https://www.codefactor.io/repository/github/jbzoo/cli/issues)    [![PHP Strict Types](https://img.shields.io/badge/strict__types-%3D1-brightgreen)](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.strict)    
[![Stable Version](https://poser.pugx.org/jbzoo/cli/version)](https://packagist.org/packages/jbzoo/cli)    [![Total Downloads](https://poser.pugx.org/jbzoo/cli/downloads)](https://packagist.org/packages/jbzoo/cli/stats)    [![Dependents](https://poser.pugx.org/jbzoo/cli/dependents)](https://packagist.org/packages/jbzoo/cli/dependents?order_by=downloads)    [![Visitors](https://visitor-badge.glitch.me/badge?page_id=jbzoo.cli)]()    [![GitHub License](https://img.shields.io/github/license/jbzoo/cli)](https://github.com/JBZoo/Cli/blob/master/LICENSE)

The library greatly extends the functionality of [Symfony/Console](https://symfony.com/doc/current/components/console.html) and helps make creating new console utilities in PHP quicker and easier.

 * Improved progress bar with a new template and additional information. See [ExamplesProgressBar.php](demo/Commands/ExamplesProgressBar.php).
 * Convert option values to a strict variable type. See [ExamplesOptionsStrictTypes.php](demo/Commands/ExamplesOptionsStrictTypes.php).
 * New built-in styles and colors for text output. See [ExamplesStyles.php](demo/Commands/ExamplesStyles.php).
 * A powerful alias `$this->_($messages, $level)` instead of `output->wrileln()`. See [ExamplesOutput.php](demo/Commands/ExamplesOutput.php).
 * Display timing and memory usage information with `--profile` option.
 * Show timestamp at the beginning of each message with `--timestamp` option.
 * Mute any sort of errors. So exit code will be always `0` (if it's possible) with `--mute-errors`. 
 * None-zero exit code on any StdErr message with `--non-zero-on-error` option.
 * For any errors messages application will use StdOut instead of StdErr `--stdout-only` option (It's on your own risk!).
 * Disable progress bar animation for logs with `--no-progress` option.

## Live Demo

[![asciicast](https://asciinema.org/a/486674.svg)](https://asciinema.org/a/486674)


## Installing

```sh
composer require jbzoo/cli
```

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


[./demo/composer.json](demo/composer.json)

<details>
  <summary>See Details</summary>

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

</details>


Binary file: [demo/my-app](demo/my-app)

<details>
  <summary>See Details</summary>

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

</details>


The simplest CLI action: [./demo/Commands/Simple.php](demo/Commands/Simple.php)

<details>
  <summary>See Details</summary>

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

</details>



## Useful projects and links

* [Symfony/Console Docs](https://symfony.com/doc/current/components/console.html)
* [kevinlebrun/colors.php - New colors for the terminal](https://github.com/kevinlebrun/colors.php)
* [php-school/cli-menu - Interactive menu with nested items](https://packagist.org/packages/php-school/cli-menu)
* [nunomaduro/collision - Beautiful error reporting](https://github.com/nunomaduro/collision)
* [splitbrain/php-cli - Lightweight and no dependencies CLI framework](https://packagist.org/packages/splitbrain/php-cli)
* [thephpleague/climate - Allows you to easily output colored text, special formats](https://github.com/thephpleague/climate)
* [Exit Codes With Special Meanings](https://tldp.org/LDP/abs/html/exitcodes.html)


## License

MIT


## See Also

- [CI-Report-Converter](https://github.com/JBZoo/CI-Report-Converter) - Converting different error reports for deep compatibility with popular CI systems.
- [Composer-Diff](https://github.com/JBZoo/Composer-Diff) - See what packages have changed after `composer update`.
- [Composer-Graph](https://github.com/JBZoo/Composer-Graph) - Dependency graph visualization of composer.json based on mermaid-js.
- [Mermaid-PHP](https://github.com/JBZoo/Mermaid-PHP) - Generate diagrams and flowcharts with the help of the mermaid script language.
- [Utils](https://github.com/JBZoo/Utils) - Collection of useful PHP functions, mini-classes, and snippets for every day.
- [Image](https://github.com/JBZoo/Image) - Package provides object-oriented way to manipulate with images as simple as possible.
- [Data](https://github.com/JBZoo/Data) - Extended implementation of ArrayObject. Use files as config/array. 
- [Retry](https://github.com/JBZoo/Retry) - Tiny PHP library providing retry/backoff functionality with multiple backoff strategies and jitter support.
- [SimpleTypes](https://github.com/JBZoo/SimpleTypes) - Converting any values and measures - money, weight, exchange rates, length, ...
