<?php

declare(strict_types=1);

namespace Antidot\Installer\Template;

use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Json\JsonManipulator;

use function exec;
use function file_get_contents;
use function file_put_contents;
use function preg_replace;
use function sprintf;

class ComposerJson
{
    private IOInterface $io;

    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }

    /**
     * @param string $installationPath
     * @param array<string, string> $dependencies
     * @param array<string> $removePatterns
     */
    public function prepare(string $installationPath, array $dependencies, array $removePatterns): void
    {
        // Update composer.json (project is proprietary by default)
        /** @psalm-suppress MixedArgument */
        $json = new JsonFile(Factory::getComposerFile());
        $contents = file_get_contents($json->getPath());
        $manipulator = new JsonManipulator($contents);
        // new projects are most of the time proprietary
        $manipulator->addMainKey('license', 'proprietary');

        foreach ($dependencies as $package => $version) {
            $manipulator->addLink('require', $package, $version);
        }

        /** @psalm-suppress MixedArgument */
        $contents = preg_replace($removePatterns, '', $manipulator->getContents(), 1);

        $namespace = $this->io->ask('Select the primary namespace for your application [<info>App</info>]: ', 'App');
        $contents = preg_replace(
            [
                '{(^\s*+")App(\\\\\\\\":\s.*$\n)}m',
                '{(^\s*+")App(\\\\\\\\Test\\\\\\\\":\s.*$\n)}m',
            ],
            '$1' . $namespace . '$2',
            $contents,
            1
        );

        file_put_contents($installationPath . '/composer.json', $contents);

        exec(sprintf('cd %s && rm -rf vendor/ composer.lock && composer install  --ansi', $installationPath));
    }
}
