<?php

declare(strict_types=1);

namespace Antidot\Installer\ApplicationType;

use Antidot\Installer\Question\InstallationPath;
use Antidot\Installer\Template\ComposerJson;
use Antidot\Installer\Template\Micro\FileStructure;
use Composer\Composer;
use Composer\IO\IOInterface;

use function dirname;
use function exec;
use function sprintf;
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

    public function install(IOInterface $io, Composer $composer): void
    {
        $installationPathQuestion = new InstallationPath($io);
        $installationPath = $installationPathQuestion->ask(
            dirname($composer->getInstallationManager()->getInstallPath($composer->getPackage()), 3) . '/'
        );

        $fileStructure = new FileStructure();
        $fileStructure->create($installationPath);

        foreach (self::COMMUNITY_FILES as $fileToDelete) {
            @unlink($installationPath . $fileToDelete);
        }

        $manipulator = new ComposerJson($io);
        $manipulator->prepare($installationPath, self::DEPENDENCIES, [
            '{^\s*+"name":.*,$\n}m',
            '{^\s*+"description":.*,$\n}m',
            '{^\s*+"antidot-fw\/installer":.*,$\n}m',
            '{^\s*+"repositories":.*$\n^\s*+\{$\n^\s*+.*,$\n^\s*+.*$\n^\s*+\}$\n^\s*+\],$\n}m' // only for development
        ]);

        exec(sprintf('cd %s && rm -rf vendor/ composer.lock && composer install  --ansi', $installationPath));
    }
}
