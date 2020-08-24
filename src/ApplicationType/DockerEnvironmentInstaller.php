<?php

declare(strict_types=1);

namespace Antidot\Installer\ApplicationType;

use Antidot\Installer\Question\UserWantsDocker;
use Antidot\Installer\Template\Docker\FileStructure;
use Antidot\Installer\Template\FileStructureFactory;
use Composer\IO\IOInterface;

class DockerEnvironmentInstaller implements App
{
    private UserWantsDocker $userWantsDockerQuestion;
    private FileStructureFactory $fileStructure;

    public function __construct(IOInterface $io)
    {
        $this->userWantsDockerQuestion = new UserWantsDocker($io);
        $this->fileStructure = new FileStructure();
    }

    public function install(string $installationPath): void
    {
        $userWantsDocker = $this->userWantsDockerQuestion->ask();
        if (false === $userWantsDocker) {
            return;
        }

        $this->fileStructure->create($installationPath);
    }
}
