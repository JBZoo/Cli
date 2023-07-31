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

namespace JBZoo\Cli\OutputMods;

use JBZoo\Cli\CliApplication;
use JBZoo\Cli\CliHelper;
use JBZoo\Cli\OutLvl;
use JBZoo\Cli\ProgressBars\AbstractProgressBar;
use JBZoo\Utils\Slug;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Logstash extends AbstractOutputMode
{
    public const NAME        = 'logstash';
    public const DESCRIPTION = 'Logstash output format, for integration with ELK stack.';

    private Logger $logger;

    public function __construct(InputInterface $input, OutputInterface $output, CliApplication $application)
    {
        parent::__construct($input, $output, $application);

        $this->output->getFormatter()->setDecorated(false);
        $this->errOutput->getFormatter()->setDecorated(false);

        $handler = new StreamHandler('php://stdout', OutLvl::mapToPsrLevel($output->getVerbosity()));
        $handler->setFormatter(new LogstashFormatter('cli'));

        $this->logger = new Logger(Slug::filter($this->application->getName()));
        $this->logger->pushHandler($handler);
    }

    public function onExecBefore(): void
    {
        $this->_('Command Start: ' . $this->input->getFirstArgument(), OutLvl::INFO, [
            'service' => [
                'name'    => $this->application->getName(),
                'version' => $this->application->getVersion(),
                'type'    => 'php',
            ],
            'process' => [
                'pid'               => \getmypid(),
                'executable'        => $_SERVER['PHP_SELF'] ?? null,
                'args_count'        => $_SERVER['argv'] ?? null,
                'command_line'      => $this->input->__toString(),
                'process_command'   => $this->input->getFirstArgument(),
                'args'              => $this->input->getArguments() + $this->input->getOptions(),
                'working_directory' => \getcwd(),
            ],
        ]);
    }

    public function onExecException(\Exception $exception): void
    {
        $this->_('Command Exception: ' . $exception->getMessage(), OutLvl::EXCEPTION, [
            'error' => self::exceptionToLog($exception),
        ]);
    }

    public function onExecAfter(int $exitCode): void
    {
        $this->_('Command Finish: ' . $exitCode, OutLvl::INFO, [
            'process' => ['exit_code' => $exitCode],
        ]);
    }

    public function isProgressBarDisabled(): bool
    {
        return false;
    }

    protected function printMessage(
        ?string $message = '',
        string $verboseLevel = OutLvl::DEFAULT,
        array $context = [],
    ): void {
        $this->logger->log(OutLvl::mapToPsrLevel($verboseLevel), $message, $context);
    }

    protected function prepareContext(array $context): array
    {
        $newContext = CliHelper::arrayMergeRecursiveOverwrite([
            'trace'   => ['id' => CliHelper::createOrGetTraceId()],
            'profile' => $this->getProfileInfo(),
        ], $context);

        return parent::prepareContext($newContext);
    }

    private static function exceptionToLog(?\Exception $exception): ?array
    {
        static $deepCounter = 0;

        if ($exception === null) {
            return null;
        }

        $maxExceptionDeepLevel = 5;
        if ($deepCounter === $maxExceptionDeepLevel) {
            return [
                'message'  => $exception?->getMessage(),
                'previous' => 'too deep',
            ];
        }

        if ($exception instanceof \Exception) {
            $deepCounter++;

            return [
                'type'        => \get_class($exception),
                'code'        => $exception->getCode(),
                'message'     => $exception->getMessage(),
                'file'        => $exception->getFile() . ':' . $exception->getLine(),
                'stack_trace' => $exception->getTraceAsString(),
                'previous'    => self::exceptionToLog($exception->getPrevious()),
            ];
        }

        return null;
    }

    public function createProgressBar(): AbstractProgressBar
    {
    }
}
