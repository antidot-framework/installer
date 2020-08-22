<?php

declare(strict_types=1);

namespace Antidot\Installer\ApplicationType;

use Antidot\Installer\Question\InstallationPath;
use Antidot\Installer\Template\Micro\FileStructure;
use Composer\Composer;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Json\JsonManipulator;

use function dirname;
use function exec;
use function sprintf;
use function unlink;

class MicroAppInstaller implements App
{
    public const DEPENDENCIES = [
        'antidot-fw/framework' => 'dev-master',//'^0.1.2',
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

        // Update composer.json (project is proprietary by default)
        /** @psalm-suppress MixedArgument */
        $json = new JsonFile(Factory::getComposerFile());
        $contents = file_get_contents($json->getPath());
        $manipulator = new JsonManipulator($contents);

        // new projects are most of the time proprietary
        $manipulator->addMainKey('license', 'proprietary');

        foreach (self::DEPENDENCIES as $package => $version) {
            $manipulator->addLink('require', $package, $version);
        }

        /** @psalm-suppress MixedArgument */
        $contents = preg_replace(
            [
                '{^\s*+"name":.*,$\n}m',
                '{^\s*+"description":.*,$\n}m',
                '{^\s*+"antidot-fw\/installer":.*,$\n}m',
                '{^\s*+"repositories":.*$\n^\s*+\{$\n^\s*+.*,$\n^\s*+.*$\n^\s*+\}$\n^\s*+\],$\n}m'
            ],
            '',
            $manipulator->getContents(),
            1
        );
        file_put_contents($installationPath . '/composer.json', $contents);

        exec(sprintf('cd %s && rm -rf vendor/ composer.lock && composer install  --ansi', $installationPath));
    }
}
