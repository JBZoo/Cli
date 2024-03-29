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
use JBZoo\Cli\ProgressBars\ProgressBarLight;
use JBZoo\Utils\Slug;
use Monolog\DateTimeImmutable;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function JBZoo\Utils\bool;

class Logstash extends AbstractOutputMode
{
    private Logger $logger;

    public function __construct(InputInterface $input, OutputInterface $output, CliApplication $application)
    {
        $output->getFormatter()->setDecorated(false);

        $handler = new StreamHandler('php://stdout', OutLvl::mapToMonologLevel($output->getVerbosity()));
        $handler->setFormatter(new LogstashFormatter('cli'));

        $this->logger = new Logger(Slug::filter($application->getName()));
        $this->logger->pushHandler($handler);

        parent::__construct($input, $output, $application);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function onExecBefore(): void
    {
        $this->_('Command Start: ' . (string)$this->input->getFirstArgument(), OutLvl::INFO, [
            'service' => [
                'name'        => $this->application->getName(),
                'version'     => $this->application->getVersion(),
                'type'        => 'php',
                'php_version' => \PHP_VERSION,
            ],
            'process' => [
                'pid'               => \getmypid(),
                'executable'        => $_SERVER['PHP_SELF'] ?? null,
                'command_line'      => $this->input->__toString(),
                'process_command'   => $this->input->getFirstArgument(),
                'working_directory' => \getcwd(),
            ],
        ]);
    }

    public function onExecException(\Exception $exception): void
    {
        $this->logger->log(
            Level::Critical,
            'Command Exception: ' . $exception->getMessage(),
            $this->prepareContext([
                'error' => [
                    'type'        => \get_class($exception),
                    'code'        => $exception->getCode(),
                    'message'     => $exception->getMessage(),
                    'file'        => $exception->getFile() . ':' . $exception->getLine(),
                    'stack_trace' => $exception->getTraceAsString(),
                ],
            ]),
        );
    }

    public function onExecAfter(int $exitCode, ?string $outputLevel = null): void
    {
        $outputLevel ??= OutLvl::INFO;
        $this->_('Command Finish: ExitCode=' . $exitCode, $outputLevel, [
            'process' => ['exit_code' => $exitCode],
        ]);
    }

    public function isProgressBarDisabled(): bool
    {
        return false;
    }

    public function createProgressBar(): AbstractProgressBar
    {
        return new ProgressBarLight($this);
    }

    public static function getName(): string
    {
        return 'logstash';
    }

    public static function getDescription(): string
    {
        return 'Logstash output format, for integration with ELK stack.';
    }

    protected function printMessage(
        ?string $message = '',
        string $verboseLevel = OutLvl::DEFAULT,
        array $context = [],
    ): void {
        $nonZeroOnError = bool($this->getInput()->getOption('non-zero-on-error'));
        $psrErrorLevel  = OutLvl::mapToMonologLevel($verboseLevel);

        if ($nonZeroOnError && OutLvl::isPsrErrorLevel($psrErrorLevel)) {
            $this->markOutputHasErrors(true);
        }

        if ($message !== null && $message !== '') {
            $this->logger->log($psrErrorLevel, \strip_tags($message), $context);
        }
    }

    protected function prepareContext(array $context): array
    {
        // We use timestamp_real to use the value from it in @timestamp using the rules of the logstash service.
        // In cases if the default field `@timestamp` doesn't work with the logstash service for some reason.
        if (
            \defined('JBZOO_CLI_TIMESTAMP_REAL')
            && JBZOO_CLI_TIMESTAMP_REAL
            && !isset($context['timestamp_real'])
        ) {
            $context['timestamp_real'] = new DateTimeImmutable(true, new \DateTimeZone(\date_default_timezone_get()));
        }

        $newContext = [
            'trace'   => ['id' => CliHelper::createOrGetTraceId()],
            'profile' => $this->getProfileInfo(),
        ] + $context;

        return parent::prepareContext($newContext);
    }
}
