<?php

declare(strict_types=1);

namespace Antidot\Installer\Question;

use Composer\IO\IOInterface;

use InvalidArgumentException;

use function dirname;
use function is_dir;
use function sprintf;
use function substr;

class InstallationPath
{
    private IOInterface $io;

    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }

    public function ask(string $installationPath): string
    {
        do {
            $isValidInstallationPath = $this->io->askConfirmation(
                sprintf('The application will be installed at "%s" directory [Y/N]: ', $installationPath),
                true
            );
            if (false === $isValidInstallationPath) {
                $installationPath = trim($this->io->ask(
                    'Add the absolute path to install the project [/opt/app]: ',
                    '/opt/app'
                ));
            }
        } while (false === $isValidInstallationPath);

        if (false === is_dir($installationPath)) {
            throw new InvalidArgumentException('Invalid installation path given.');
        }

        return substr($installationPath, -1) === '/'
            ? substr($installationPath, 0, -1)
            : $installationPath;
    }
}
