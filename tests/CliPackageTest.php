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

namespace JBZoo\PHPUnit;

final class CliPackageTest extends \JBZoo\Codestyle\PHPUnit\AbstractPackageTest
{
    protected string $packageName = 'Cli';

    protected function setUp(): void
    {
        parent::setUp();

        $this->excludePaths[] = '.github/assets';
    }

    protected static function phpVersions(): array
    {
        return [8.2, 8.3, 8.4];
    }

    protected static function uploadArtifactsStep(string $stepName): array
    {
        return [
            'name'              => 'Upload Artifacts',
            'uses'              => 'actions/upload-artifact@v4',
            'continue-on-error' => true,
            'with'              => ['name' => $stepName, 'path' => 'build/'],
        ];
    }
}
