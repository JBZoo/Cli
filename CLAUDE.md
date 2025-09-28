# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

JBZoo/Cli is a PHP library that extends Symfony Console functionality for creating CLI applications. It provides enhanced progress bars, strict type conversion for options, styling, multiple output modes, profiling, and multiprocessing capabilities.

## Development Commands

### Testing and Quality Assurance
- `make test-all` - Run all project tests including codestyle checks
- `make test` - Run PHPUnit tests
- `make codestyle` - Run code style checks on src/
- `make codestyle PATH_SRC=./demo` - Run code style checks on demo/
- `phpunit` - Run unit tests directly (uses phpunit.xml.dist)

### Dependencies
- `make update` - Install/update all 3rd party dependencies via Composer
- `composer update` - Update Composer dependencies

### Static Analysis
- `phpstan` - Run PHPStan static analysis (uses phpstan.neon config)

### Demo Testing
- `make test-logstash` - Run Logstash manual tests and output to build/logstash.log

## Architecture

### Core Classes
- `CliApplication` - Main application class that extends Symfony Application
  - Registers commands by directory path scanning
  - Manages output modes and event handling
  - Located in `src/CliApplication.php`

- `CliCommand` - Base class for all CLI commands that extends Symfony Command
  - Provides enhanced option parsing with strict types (`getOptInt`, `getOptBool`, `getOptString`, etc.)
  - Built-in progress bar functionality via `progressBar()` method
  - Output method aliases (`$this->_()` instead of `$output->writeln()`)
  - Located in `src/CliCommand.php`

- `CliCommandMultiProc` - Extension for multiprocessing commands
  - Implements `executeOneProcess()` and `getListOfChildIds()` methods
  - Located in `src/CliCommandMultiProc.php`

### Output Modes
Three output modes in `src/OutputMods/`:
- `Text` - Default user-friendly output
- `Cron` - Timestamped logs for crontab (combines `--timestamp --profile --stdout-only --no-progress -vv --no-ansi`)
- `Logstash` - JSON format for ELK Stack integration

### Progress Bars
Enhanced progress bar system in `src/ProgressBars/`:
- `ProgressBar` - Main progress bar implementation
- `ProgressBarLight` - Lightweight version
- `ProgressBarSymfony` - Symfony-compatible wrapper
- `ProgressBarProcessManager` - For multiprocessing scenarios

### Utility Classes
- `CliHelper` - Helper functions and utilities
- `CliRender` - Rendering utilities for lists and formatting
- `Codes` - Exit code constants
- `OutLvl` - Output verbosity level constants
- `Icons` - Icon constants for CLI output

## Command Development Patterns

### Basic Command Structure
Commands should extend `CliCommand` and implement `executeAction()` method. Use the demo commands in `demo/Commands/` as examples:
- `DemoSimple.php` - Basic command structure
- `DemoProgressBar.php` - Progress bar usage
- `DemoOutput.php` - Output and verbosity examples
- `DemoOptionsStrictTypes.php` - Strict type option parsing

### Command Registration
Commands are auto-discovered by `CliApplication::registerCommandsByPath()` which scans directories for PHP files extending `CliCommand`.

### Option Handling
Use strict type methods instead of raw option values:
- `$this->getOptInt('option-name')` instead of `$this->getOption('option-name')`
- `$this->getOptBool()`, `$this->getOptString()`, `$this->getOptArray()`, `$this->getOptDatetime()`

### Output Methods
- Use `$this->_($message, $verboseLevel, $context)` for output
- Use global `cli($message, $verboseLevel, $context)` function outside command classes
- Use `$this->progressBar()` for loops with progress tracking

## File Structure
- `src/` - Main library code
- `demo/` - Example CLI application showing library features
- `tests/` - PHPUnit tests
- `vendor/` - Composer dependencies

## PSR-4 Autoloading
- `JBZoo\Cli\` maps to `src/`
- `JBZoo\PHPUnit\` maps to `tests/` (dev)