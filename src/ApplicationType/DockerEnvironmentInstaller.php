<?php

declare(strict_types=1);

namespace Antidot\Installer\ApplicationType;

use Antidot\Installer\Question\UserWantsDocker;
use Antidot\Installer\Template\Docker\FileStructure;
use Antidot\Installer\Template\FileStructureFactory;
use Composer\IO\IOInterface;
use RuntimeException;

class DockerEnvironmentInstaller implements App
{
    private UserWantsDocker $userWantsDockerQuestion;
    private FileStructureFactory $fileStructure;

    public function __construct(IOInterface $io)
    {
        $this->userWantsDockerQuestion = new UserWantsDocker($io);
        $this->fileStructure = new FileStructure();
    }

    public function install(?string $installationPath = null): void
    {
        $userWantsDocker = $this->userWantsDockerQuestion->ask();
        if (false === $userWantsDocker) {
            return;
        }

        if (null === $installationPath) {
            throw new RuntimeException('Cannot install Docker environment without installation path.');
        }

        $this->fileStructure->create($installationPath);
    }
}
