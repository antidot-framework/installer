<?php

declare(strict_types=1);

namespace Antidot\Installer\ApplicationType;

use Antidot\Installer\Question\InstallationPath;
use Antidot\Installer\Template\ComposerJson;
use Antidot\Installer\Template\Micro\FileStructure;
use Composer\Composer;
use Composer\IO\IOInterface;

use function dirname;
use function file_exists;
use function unlink;

class MicroAppInstaller implements App
{
    public const DEPENDENCIES = [
        'antidot-fw/framework' => '^0.1.2',
        'antidot-fw/container' => '^0.1.1',
        'antidot-fw/fast-router-adapter' => '^0.1.0',
    ];

    public const COMMUNITY_FILES = [
        '/CHANGELOG.md',
        '/CODE_OF_CONDUCT.md',
        '/CONTRIBUTING.md',
        '/PULL_REQUEST_TEMPLATE.md',
        '/LICENSE',
    ];
    private IOInterface $io;
    private Composer $composer;
    private InstallationPath $installationPathQuestion;
    private FileStructure $fileStructure;
    private ComposerJson $manipulator;

    public function __construct(IOInterface $io, Composer $composer, ComposerJson $manipulator)
    {
        $this->composer = $composer;
        $this->installationPathQuestion = new InstallationPath($io);
        $this->fileStructure = new FileStructure();
        $this->manipulator = $manipulator;
    }

    public function install(): void
    {
        $installationPath = $this->installationPathQuestion->ask(
            dirname($this->composer->getInstallationManager()->getInstallPath(
                $this->composer->getPackage()
            ), 3) . '/'
        );

        $this->fileStructure->create($installationPath);

        foreach (self::COMMUNITY_FILES as $fileToDelete) {
            $filePath = $installationPath . $fileToDelete;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $this->manipulator->prepare($installationPath, self::DEPENDENCIES, []);
    }
}
