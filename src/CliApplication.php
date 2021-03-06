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

namespace JBZoo\Cli;

use JBZoo\Event\EventManager;
use JBZoo\Utils\FS;
use Symfony\Component\Console\Application;

/**
 * Class CliApplication
 * @package JBZoo\Cli
 */
class CliApplication extends Application
{
    /**
     * @var EventManager|null
     */
    private ?EventManager $eventManager = null;

    /**
     * @var string|null
     */
    private ?string $logo = null;

    /**
     * Register commands by directory path
     *
     * @param string $commandsDir
     * @param string $gloabalNamespace
     * @param bool   $strictMode
     * @return \JBZoo\Cli\CliApplication
     * @throws \ReflectionException
     */
    public function registerCommandsByPath(
        string $commandsDir,
        string $gloabalNamespace,
        bool $strictMode = true
    ): self {
        if ($strictMode && !\is_dir($commandsDir)) {
            throw new Exception('First argument is not directory!');
        }

        $files = FS::ls($commandsDir);

        if (empty($files)) {
            return $this;
        }

        foreach ($files as $file) {
            if (!\file_exists($file)) {
                continue;
            }

            require_once $file;

            $taskNamespace = \trim(\str_replace('/', '\\', (string)\strstr(\dirname($file), 'Commands')));
            $commandClassName = "{$gloabalNamespace}\\{$taskNamespace}\\" . FS::filename($file);

            if (\class_exists($commandClassName)) {
                $reflection = new \ReflectionClass($commandClassName);
            } else {
                throw new Exception("Command/Class \"{$commandClassName}\" can'be loaded from the file \"{$file}\"");
            }

            if (!$reflection->isAbstract() && $reflection->isSubclassOf(CliCommand::class)) {
                /** @var CliCommand $command */
                $command = $reflection->newInstance();
                $this->add($command);
            }
        }

        return $this;
    }

    /**
     * @param EventManager $eventManager
     * @return $this
     */
    public function setEventManager(EventManager $eventManager): self
    {
        $this->eventManager = $eventManager;
        return $this;
    }

    /**
     * @return EventManager|null
     */
    public function getEventManager(): ?EventManager
    {
        return $this->eventManager;
    }

    /**
     * @param string|null $logo
     * @return $this
     */
    public function setLogo(?string $logo = null): self
    {
        $this->logo = $logo;
        return $this;
    }

    /**
     * Returns the long version of the application.
     *
     * @return string The long application version
     */
    public function getLongVersion(): string
    {
        if ($this->logo) {
            return "<info>{$this->logo}</info>\n<comment>{$this->getVersion()}</comment>";
        }

        return parent::getLongVersion();
    }
}
