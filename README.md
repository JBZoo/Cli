# JBZoo / Cli

[![CI](https://github.com/JBZoo/Cli/actions/workflows/main.yml/badge.svg?branch=master)](https://github.com/JBZoo/Cli/actions/workflows/main.yml?query=branch%3Amaster)    [![Coverage Status](https://coveralls.io/repos/github/JBZoo/Cli/badge.svg?branch=master)](https://coveralls.io/github/JBZoo/Cli?branch=master)    [![Psalm Coverage](https://shepherd.dev/github/JBZoo/Cli/coverage.svg)](https://shepherd.dev/github/JBZoo/Cli)    [![Psalm Level](https://shepherd.dev/github/JBZoo/Cli/level.svg)](https://shepherd.dev/github/JBZoo/Cli)    [![CodeFactor](https://www.codefactor.io/repository/github/jbzoo/cli/badge)](https://www.codefactor.io/repository/github/jbzoo/cli/issues)    
[![Stable Version](https://poser.pugx.org/jbzoo/cli/version)](https://packagist.org/packages/jbzoo/cli/)    [![Total Downloads](https://poser.pugx.org/jbzoo/cli/downloads)](https://packagist.org/packages/jbzoo/cli/stats)    [![Dependents](https://poser.pugx.org/jbzoo/cli/dependents)](https://packagist.org/packages/jbzoo/cli/dependents?order_by=downloads)    [![GitHub License](https://img.shields.io/github/license/jbzoo/cli)](https://github.com/JBZoo/Cli/blob/master/LICENSE)


<!--ts-->
   * [Why?](#why)
   * [Live Demo](#live-demo)
      * [Output regular messages](#output-regular-messages)
      * [Progress Bar Demo](#progress-bar-demo)
   * [Quck Start - Build your first CLI App](#quck-start---build-your-first-cli-app)
      * [Installing](#installing)
      * [File Structure](#file-structure)
      * [Composer file](#composer-file)
      * [Binary file](#binary-file)
      * [Simple CLI Action](#simple-cli-action)
   * [Built-in Functionality](#built-in-functionality)
      * [Sanitize input variables](#sanitize-input-variables)
      * [Rendering text in different colors and styles](#rendering-text-in-different-colors-and-styles)
      * [Verbosity Levels](#verbosity-levels)
      * [Memory and time profiling](#memory-and-time-profiling)
   * [Progress Bar](#progress-bar)
      * [Simple example](#simple-example)
      * [Advanced usage](#advanced-usage)
   * [Helper Functions](#helper-functions)
      * [Regualar question](#regualar-question)
      * [Ask user's password](#ask-users-password)
      * [Ask user to select the option](#ask-user-to-select-the-option)
      * [Represent a yes/no question](#represent-a-yesno-question)
      * [Rendering key=&gt;value list](#rendering-keyvalue-list)
   * [Easy logging](#easy-logging)
      * [Simple log](#simple-log)
      * [Crontab](#crontab)
      * [Elatcisearch / Logstash (ELK)](#elatcisearch--logstash-elk)
   * [Multi processing](#multi-processing)
   * [Tips &amp; Tricks](#tips--tricks)
   * [Contributing](#contributing)
   * [Useful projects and links](#useful-projects-and-links)
   * [License](#license)
   * [See Also](#see-also)
<!--te-->

## Why?

The library greatly extends the functionality of CLI App and helps make creating new console utilities in PHP quicker and easier. Here's a summary of why this library is essential:

 * **Enhanced Functionality**
   * The library supercharges [Symfony/Console](https://symfony.com/doc/current/components/console.html), facilitating a more streamlined development of console utilities. 

 * **Progress Bar Improvements**
   * Developers gain a refined progress bar suited for loop actions and enhanced with debugging information. This makes tracking task progress and diagnosing issues a breeze. See [DemoProgressBar.php](demo/Commands/DemoProgressBar.php) and see [Live Demo](https://asciinema.org/a/601633?autoplay=1&startAt=4).
   * `$this->_($messages, $level, $context)` as part of CliCommand instead of Symfony/Console `$output->writeln()`.
   * `cli($messages, $level, $context)` as alias for different classes.
   * `$this->progressBar(iterable|int $listOrMax, \Closure $callback, string $title = '')` as part of CliCommand instead of Symfony/Console ProgressBar.

 * **Strict Type Conversion**
   * One notable feature allows for the strict conversion of option values, ensuring data integrity and reducing runtime errors. See [DemoOptionsStrictTypes.php](demo/Commands/DemoOptionsStrictTypes.php).
   * Built-in validations for list of values. See [Sanitize input variables](#sanitize-input-variables).

 * **Styling and Output Customization**
   * With built-in styles and color schemes, developers can make their console outputs more readable and visually appealing. See [DemoStyles.php](demo/Commands/DemoStyles.php).

 * **Message Aliases**
   * The library introduces powerful aliases for message outputs, allowing for concise and consistent command calls. This is especially helpful in maintaining clean code.

 * **Advanced Options**
   * Features such as profiling for performance, timestamping, error muting, and specialized output modes (like cron and logstash modes) empower developers to refine their console outputs and diagnostics according to their specific needs.
   * Display timing and memory usage information with `--profile` option.
   * Show timestamp at the beginning of each message with `--timestamp` option.
   * Mute any sort of errors. So exit code will be always `0` (if it's possible) with `--mute-errors`.
   * None-zero exit code on any StdErr message with `--non-zero-on-error` option.
   * For any errors messages application will use StdOut instead of StdErr `--stdout-only` option (It's on your own risk!).
   * Disable progress bar animation for logs with `--no-progress` option.

 * **Versatile Output Modes** 
   * The library provides different output formats catering to various use cases. Whether you're focusing on user-friendly text, logs, or integration with tools like ELK Stack, there's an output mode tailored for you.
   * `--output-mode=text`. By default, text output format. Userfriendly and easy to read.
   * `--output-mode=cron`. It's basically focused on logs output. It's combination of `--timestamp --profile --stdout-only --no-progress -vv --no-ansi`.
   * `--output-mode=logstash`. It's basically focused on Logstash format for ELK Stack. Also, it means `--stdout-only --no-progress -vv`.

 * **Bonuses**
   * There is a [multiprocess](#multi-processing) mode (please don't confuse it with multithreading) to speed up work with a monotonous dataset.
   * Helper functions for [user input](#helper-functions) in interactive mode.



## Live Demo

### Output regular messages

[![asciicast](https://asciinema.org/a/601633.svg)](https://asciinema.org/a/601633?autoplay=1&startAt=4)



### Progress Bar Demo

[![asciicast](https://asciinema.org/a/601621.svg)](https://asciinema.org/a/601621?autoplay=1&startAt=2)



## Quck Start - Build your first CLI App

### Installing

```sh
composer require jbzoo/cli
```


The simplest CLI application has the following file structure. See the [Demo App](demo) for more details.


### File Structure
```
/path/to/app/
    my-app                      # Binrary file (See below)
    composer.json               # Composer file
    /Commands/                  # Commands directory
        Simple.php              # One of the commands (See below)
    /vendor/
        autoload.php            # Composer autoload
```


### Composer file

[./demo/composer.json](demo/composer.json)

<details>
  <summary>See Details</summary>

  ```json
  {
      "name"        : "vendor/my-app",
      "type"        : "project",
      "description" : "Example of CLI App based on JBZoo/CLI",
      "license"     : "MIT",
      "keywords"    : ["cli", "application", "example"],
  
      "require"     : {
          "php"       : ">=8.1",
          "jbzoo/cli" : "^7.1.0"
      },
  
      "autoload"    : {
          "psr-4" : {"DemoApp\\" : ""}
      },
  
      "bin"         : ["my-app"]
  }
  ```

</details>


### Binary file

Binary file: [demo/my-app](demo/my-app)

<details>
  <summary>See Details</summary>

  ```php
  #!/usr/bin/env php
<?php declare(strict_types=1);

namespace DemoApp;

use JBZoo\Cli\CliApplication;

// Init composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Optional. Set your application name and version.
$application = new CliApplication('My Console Application', 'v1.0.0');

// Optional. Looks at the online generator of ASCII logos
// https://patorjk.com/software/taag/#p=testall&f=Epic&t=My%20Console%20App
$application->setLogo(
    <<<'EOF'
          __  __          _____                      _
         |  \/  |        / ____|                    | |          /\
         | \  / |_   _  | |     ___  _ __  ___  ___ | | ___     /  \   _ __  _ __
         | |\/| | | | | | |    / _ \| '_ \/ __|/ _ \| |/ _ \   / /\ \ | '_ \| '_ \
         | |  | | |_| | | |___| (_) | | | \__ \ (_) | |  __/  / ____ \| |_) | |_) |
         |_|  |_|\__, |  \_____\___/|_| |_|___/\___/|_|\___| /_/    \_\ .__/| .__/
                  __/ |                                               | |   | |
                 |___/                                                |_|   |_|
        EOF,
);

// Scan directory to find commands.
//  * It doesn't work recursively!
//  * They must be inherited from the class \JBZoo\Cli\CliCommand
$application->registerCommandsByPath(__DIR__ . '/Commands', __NAMESPACE__);

// Optional. Action name by default (if there is no arguments)
$application->setDefaultCommand('list');

// Run application
$application->run();
  
  ```

</details>


### Simple CLI Action

The simplest CLI action: [./demo/Commands/DemoSimple.php](demo/Commands/DemoSimple.php)

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
          return self::SUCCESS;
      }
  }
  ```

</details>



## Built-in Functionality

### Sanitize input variables

As live-demo take a look at demo application - [./demo/Commands/DemoOptionsStrictTypes.php](demo/Commands/DemoOptionsStrictTypes.php).

Try to launch `./my-app options-strict-types`.

```php
// If the option has `InputOption::VALUE_NONE` it returns true/false.
// --option-name
$value = $this->getOpt('option-name'); // `$value === true` 

// --option-name="    123.6   "
$value = $this->getOpt('option-name'); // Returns the value AS-IS. `$value ===  "   123.6   "`

// --option-name="    123.6   "
$value = $this->getOptBool('option-name'); // Converts an input variable to boolean. `$value === true`

// --option-name="    123.6   "
$value = $this->getOptInt('option-name'); // Converts an input variable to integer. `$value === 123`
$value = $this->getOptInt('option-name', 42, [1, 2, 42]); // Strict comparing with allowed values

// --option-name="    123.6   "
$value = $this->getOptFloat('option-name'); // Converts an input variable to float. `$value === 123.6`
$value = $this->getOptFloat('option-name', 1.0, [1.0, 2.0, 3.0]); // Strict comparing with allowed values

// --option-name="    123.6   "
$value = $this->getOptString('option-name'); // Converts an input variable to trimmed string. `$value === "123.6"`
$value = $this->getOptString('option-name', 'default', ['default', 'mini', 'full']); // Strict comparing with allowed values

// --option-name=123.6
$value = $this->getOptArray('option-name'); // Converts an input variable to trimmed string. `$value === ["123.6"]`

// --option-name="15 July 2021 13:48:00"
$value = $this->getOptDatetime('option-name'); // Converts an input variable to \DateTimeImmutable object.

// Use standard input as input variable.
// Example. `echo " Qwerty 123 " | php ./my-app agruments`
$value = self::getStdIn(); // Reads StdIn as string value. `$value === " Qwerty 123 \n"`
```



### Rendering text in different colors and styles

<img alt="output-styles" src=".github/assets/output-styles.gif" width="600"/>

There are list of predefined colors

```html
<black>  Text in Black color  </black>
<red>    Text in Red Color    </red>
<green>  Text in Green Color  </green>
<yellow> Text in Yellow Color </yellow>
<blue>   Text in Blue Color   </blue>
<magenta>Text in Magenta Color</magenta>
<cyan>   Text in Cyan Color   </cyan>
<white>  Text in White Color  </white>

<!-- Usually default color is white. It depends on terminal settings. -->
<!-- You should use it only to overwrite nested tags. -->
<default>Text in Default Color</default>
```

There are list of predefined styles

```html
<bl>Blinked Text</bl>
<b>Bold Text</b>
<u>Underlined Text</u>
<r>Reverse Color/Backgroud</r>
<bg>Change Background Only</bg>
```

Also, you can combine colors ans styles.

```html
<magenta-bl>Blinked text in magenta color</magenta-bl>
<magenta-b>Bold text in magenta color</magenta-b>
<magenta-u>Underlined text in magenta color</magenta-u>
<magenta-r>Reverse text in magenta color</magenta-r>
<magenta-bg>Reverse only background of text in magenta color</magenta-bg>
```

And predefined shortcuts for standard styles of Symfony Console

```html
<i> alias for <info>
<c> alias for <commnet>
<q> alias for <question>
<e> alias for <error>
```



### Verbosity Levels

Console commands have different verbosity levels, which determine the messages displayed in their output. 

As live-demo take a look at demo application - [./demo/Commands/ExamplesOutput.php](demo/Commands/DemoOutput.php). You can see [Demo video](https://asciinema.org/a/486674).

Example of usage of verbosity levels

![output-full-example](.github/assets/output-full-example.png)

```php
// There two strictly(!) recommended output ways:

/**
 * Prints a message to the output in the command class which inherits from the class \JBZoo\Cli\CliCommand
 * 
 * @param string|string[] $messages     Output message(s). Can be an array of strings or a string. Array of strings will be imploded with new line.
 * @param string          $verboseLevel is one of value form the class \JBZoo\Cli\OutLvl::*
 * @param string          $context      is array of extra info. Will be serialized to JSON and displayed in the end of the message.
 */
$this->_($messages, $verboseLevel, $context);

/**
 * This is global alias function of `$this->_(...)`.
 * It's nice to have it if you want to display a text from not CliCommand class.
 */
JBZoo\Cli\cli($messages, $verboseLevel, $context);
 
```

```bash
# Do not output any message
./my-app output -q
./my-app output --quiet

# Normal behavior, no option required. Only the most useful messages.
./my-app output 

# Increase verbosity of messages
./my-app output -v

# Display also the informative non essential messages
./my-app output -vv

# Display all messages (useful to debug errors)
./my-app output -vvv
```



### Memory and time profiling

As live-demo take a look at demo application - [./demo/Commands/DemoProfile.php](demo/Commands/DemoProfile.php).

Try to launch `./my-app profile --profile`.

![profiling](.github/assets/profiling.png)



## Progress Bar

As live-demo take a look at demo application - [./demo/Commands/DemoProgressBar.php](demo/Commands/DemoProgressBar.php) and [Live Demo](https://asciinema.org/a/601633?autoplay=1&startAt=4).

You can consider this as a substitute for the long cycles you want to profile.

Keep in mind that there is an additional overhead for memory and runtime to calculate all the extra debugging information in `--verbose` mode.



### Simple example

![progress-default-example](.github/assets/progress-default-example.gif)

```php
$this->progressBar(5, function (): void {
    // Some code in loop
});
```


### Advanced usage

![progress-full-example](.github/assets/progress-full-example.gif)

```php
$this->progressBar($arrayOfSomething, function ($value, $key, $step) {
    // Some code in loop

    if ($step === 3) {
        throw new ExceptionBreak("Something went wrong with \$value={$value}. Stop the loop!");
    }

    return "<c>Callback Args</c> \$value=<i>{$value}</i>, \$key=<i>{$key}</i>, \$step=<i>{$step}</i>";
}, 'Custom messages based on callback arguments', $throwBatchException);
```


## Helper Functions

As live-demo take a look at demo application - [./demo/Commands/DemoHelpers.php](demo/Commands/DemoHelpers.php).

Try to launch `./my-app helpers`.

JBZoo/Cli uses [Symfony Question Helper](https://symfony.com/doc/current/components/console/helpers/questionhelper.html) as base for aliases.

![helpers](.github/assets/helpers.gif)

### Regualar question

Ask any custom question and wait for a user's input. There is an option to set a default value.

```php
$yourName = $this->ask("What's your name?", 'Default Noname');
$this->_("Your name is \"{$yourName}\"");
```

### Ask user's password

Ask a question and hide the response. This is particularly convenient for passwords.
There is an option to set a random value as default value.

```php
$yourSecret = $this->askPassword("New password?", true);
$this->_("Your secret is \"{$yourSecret}\"");
```

### Ask user to select the option

If you have a predefined set of answers the user can choose from, you could use a method `askOption` which makes sure
that the user can only enter a valid string from a predefined list.
There is an option to set a default option (index or string).

```php
$selectedColor = $this->askOption("What's your favorite color?", ['Red', 'Blue', 'Yellow'], 'Blue');
$this->_("Selected color is {$selectedColor}");
```

### Represent a yes/no question

Suppose you want to confirm an action before actually executing it. Add the following to your command.

```php
$isConfirmed = $this->confirmation('Are you ready to execute the script?');
$this->_("Is confirmed: " . ($isConfirmed ? 'Yes' : 'No'));
```

### Rendering key=>value list

If you need to show an aligned list, use the following code.

```php
use JBZoo\Cli\CliRender;

$this->_(CliRender::list([
    "It's like a title",
    'Option Name' => 'Option Value',
    'Key' => 'Value',
    'Another Key #2' => 'Qwerty',
], '*')); // It's bullet character
``` 

```text
 * It's like a title
 * Option Name   : Option Value
 * Key           : Value
 * Another Key #2: Qwerty
```


## Easy logging

### Simple log

```bash
./my-app output --timestamp >> /path/to/crontab/logs/$(date +%Y-%m-%d).log 2>&1
```

![logs-simple](.github/assets/logs-simple.png)



### Crontab

Just add the `--output-mode=cron` flag and save the output to a file. Especially, this is very handy for saving logs for Crontab.

```bash
./my-app output --output-mode=cron >> /path/to/crontab/logs/$(date +%Y-%m-%d).log 2>&1
```

![logs-cron](.github/assets/logs-cron.png)



### Elatcisearch / Logstash (ELK)

Just add the `--output-mode=logstash` flag and save the output to a file. Especially, this is very handy for saving logs for ELK Stack.

```bash
./my-app output --output-mode=logstash >> /path/to/logstash/logs/$(date +%Y-%m-%d).log 2>&1
```

![logs-logstash-exception](.github/assets/logs-logstash-exception.png)


## Multi processing

There is a multiprocess mode (please don't confuse it with multithreading) to speed up work with a monotonous dataset. Basically, `JBZoo\Cli` will start a separate child process (not a thread!) for each dataset and wait for all of them to execute (like a Promise). This is how you get acceleration, which will depend on the power of your server and the data processing algorithm.

You will see a simple progress bar, but you won't be able to profile and log nicely, as it works for normal mode.

You can find examples here
 * [./tests/TestApp/Commands/TestSleepMulti.php](tests/TestApp/Commands/TestSleepMulti.php) - Parent command
 * [./tests/TestApp/Commands/TestSleep.php](tests/TestApp/Commands/TestSleep.php) - Child command


Notes:
 * Pay attention on the method `executeOneProcess()` and `getListOfChildIds()` which are used to manage child processes. They are inherited from `CliCommandMultiProc` class.
 * Optimal number of child processes is `Number of CPU cores - 1` . You can override this value by setting cli options. See them here [./src/CliCommandMultiProc.php](src/CliCommandMultiProc.php).
 * Be really careful with concurrency. It's not easy to debug. Try to use `-vvv` option to see all errors and warnings.


## Tips & Tricks

 * Use class `\JBZoo\Cli\Codes` to get all available exit codes.
 * You can add extra context to any message. It will be serialized to JSON and displayed in the end of the message. Just use `CliHelper::getInstance()->appendExtraContext(['section' => ['var' => 'value']]);`
 * You can define constant `\JBZOO_CLI_TIMESTAMP_REAL=true` to add `timestamp_real` as exta context. Sometimes it's useful for logstash if default value `@timestamp` doesn't work for you.


## Contributing

```shell
# Fork the repo and build project
make update

# Make your local changes

# Run all tests and check code style
make test-all

# Create your pull request and check all tests on GithubActions page
```



## Useful projects and links

* [Symfony/Console Docs](https://symfony.com/doc/current/components/console.html)
* [kevinlebrun/colors.php - New colors for the terminal](https://github.com/kevinlebrun/colors.php)
* [php-school/cli-menu - Interactive menu with nested items](https://packagist.org/packages/php-school/cli-menu)
* [nunomaduro/collision - Beautiful error reporting](https://github.com/nunomaduro/collision)
* [splitbrain/php-cli - Lightweight and no dependencies CLI framework](https://packagist.org/packages/splitbrain/php-cli)
* [thephpleague/climate - Allows you to easily output colored text, special formats](https://github.com/thephpleague/climate)
* [Exit Codes With Special Meanings](https://tldp.org/LDP/abs/html/exitcodes.html)
* [How to redirect standard (stderr) error in bash](https://www.cyberciti.biz/faq/how-to-redirect-standard-error-in-bash/)



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
