<?php

declare(strict_types=1);

namespace Antidot\Installer\ApplicationType;

use Antidot\Installer\Question\AdditionalPackages;
use Antidot\Installer\Template\ComposerJson;
use Antidot\Installer\Template\FileStructureFactory;
use Antidot\Installer\Template\Micro\FileStructure;
use Composer\IO\IOInterface;

use function array_merge;

class MicroAppInstaller implements App
{
    public const DEPENDENCIES = [
        'antidot-fw/framework' => '^0.1.2',
        'antidot-fw/container' => '^0.1.1',
        'antidot-fw/fast-router-adapter' => '^0.1.0',
    ];

    private FileStructureFactory $fileStructure;
    private ComposerJson $manipulator;
    private DockerEnvironmentInstaller $dockerInstaller;
    private AdditionalPackages $additionalPackages;

    public function __construct(IOInterface $io, ComposerJson $manipulator)
    {
        $this->manipulator = $manipulator;
        $this->additionalPackages = new AdditionalPackages($io);
        $this->dockerInstaller = new DockerEnvironmentInstaller($io);
        $this->fileStructure = new FileStructure();
    }

    public function install(string $installationPath): void
    {
        $additionalPackages = $this->additionalPackages->ask();
        $this->dockerInstaller->install($installationPath);
        $this->fileStructure->create($installationPath);
        $this->manipulator->prepare($installationPath, array_merge(self::DEPENDENCIES, $additionalPackages), []);
    }
}
