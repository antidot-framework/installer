<?php

declare(strict_types=1);

namespace Antidot\Installer\Question;

use Composer\IO\IOInterface;

use RuntimeException;

use function is_dir;
use function mkdir;
use function sprintf;
use function substr;

class InstallationPath
{
    private IOInterface $io;
    private bool $defaultValue;

    public function __construct(IOInterface $io, bool $defaultValue = true)
    {
        $this->io = $io;
        $this->defaultValue = $defaultValue;
    }

    public function ask(string $installationPath): string
    {
        do {
            $isValidInstallationPath = $this->io->askConfirmation(
                sprintf('The application will be installed at "%s" directory [<info>Y</info>/N]: ', $installationPath),
                $this->defaultValue
            );
            if (false === $isValidInstallationPath) {
                $installationPath = trim($this->io->ask(
                    'Add the absolute path to install the project [<info>/opt/app</info>]: ',
                    '/opt/app'
                ));
            }
        } while (false === $isValidInstallationPath);

        return substr($installationPath, -1) === '/'
            ? substr($installationPath, 0, -1)
            : $installationPath;
    }
}
