<?php

declare(strict_types=1);

namespace Antidot\Installer\Template;

use RuntimeException;

use function file_exists;
use function file_put_contents;
use function is_dir;
use function is_writable;
use function method_exists;
use function mkdir;
use function sprintf;
use function unlink;

abstract class CommonFileStructure implements FileStructureFactory
{
    public const COMMUNITY_FILES = [
        '/CHANGELOG.md',
        '/CODE_OF_CONDUCT.md',
        '/CONTRIBUTING.md',
        '/PULL_REQUEST_TEMPLATE.md',
        '/LICENSE',
    ];

    public const NOT_WRITABLE_MESSAGE = 'Given directory "%s" is not writable.';
    public const NOT_PERMISSIONS_MESSAGE = 'Directory "%s" was not created by permission issues.';

    protected function verifyInstallationPath(string $installationPath): void
    {
        if (!is_dir($installationPath) && !mkdir($dir = $installationPath, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf(self::NOT_PERMISSIONS_MESSAGE, $dir));
        }

        if (!is_writable($installationPath)) {
            throw new RuntimeException(sprintf(self::NOT_WRITABLE_MESSAGE, $installationPath));
        }
    }

    /**
     * @param string $installationPath
     * @param array<string> $directories
     */
    protected function createDirectories(string $installationPath, array $directories): void
    {
        foreach ($directories as $directory) {
            if (!mkdir($dir = sprintf('%s/%s', $installationPath, $directory), 0755, true) && !is_dir($dir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }
    }

    /**
     * @param string $installationPath
     * @param array<string, string> $files
     */
    protected function createFiles(string $installationPath, array $files): void
    {
        foreach ($files as $method => $filename) {
            file_put_contents(sprintf('%s/%s', $installationPath, $filename), $this->$method());
        }
    }

    protected function removeCommunityFiles(string $installationPath): void
    {
        foreach (self::COMMUNITY_FILES as $fileToDelete) {
            $filePath = $installationPath . $fileToDelete;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}
