<?php

declare(strict_types=1);

namespace Antidot\Installer\ApplicationType;

use Antidot\Installer\Question\InstallationPath;
use Antidot\Installer\Template\ComposerJson;
use Antidot\Installer\Template\FileStructureFactory;
use Antidot\Installer\Template\Micro\FileStructure;
use Composer\Composer;
use Composer\IO\IOInterface;

use function dirname;

class MicroAppInstaller implements App
{
    public const DEPENDENCIES = [
        'antidot-fw/framework' => '^0.1.2',
        'antidot-fw/container' => '^0.1.1',
        'antidot-fw/fast-router-adapter' => '^0.1.0',
    ];

    private Composer $composer;
    private InstallationPath $installationPathQuestion;
    private FileStructureFactory $fileStructure;
    private ComposerJson $manipulator;
    private DockerEnvironmentInstaller $dockerInstaller;

    public function __construct(IOInterface $io, Composer $composer, ComposerJson $manipulator)
    {
        $this->composer = $composer;
        $this->manipulator = $manipulator;
        $this->dockerInstaller = new DockerEnvironmentInstaller($io);
        $this->installationPathQuestion = new InstallationPath($io);
        $this->fileStructure = new FileStructure();
    }

    public function install(): void
    {
        $installationPath = $this->installationPathQuestion->ask(
            dirname($this->composer->getInstallationManager()->getInstallPath(
                $this->composer->getPackage()
            ), 3) . '/'
        );

        $this->dockerInstaller->install($installationPath);
        $this->fileStructure->create($installationPath);
        $this->manipulator->prepare($installationPath, self::DEPENDENCIES, []);
    }
}
