<?php

declare(strict_types=1);

namespace Antidot\Installer\Question;

use Composer\IO\IOInterface;

use function sprintf;
use function substr;

class InstallationPath
{
    private IOInterface $io;
    private bool $defaultAnswer;

    public function __construct(IOInterface $io, bool $defaultAnswer = true)
    {
        $this->io = $io;
        $this->defaultAnswer = $defaultAnswer;
    }

    public function ask(string $installationPath): string
    {
        do {
            $isValidInstallationPath = $this->io->askConfirmation(
                sprintf(
                    'The application will be installed at "<info>%s</info>" directory [<info>Y</info>/N]: ',
                    $installationPath
                ),
                $this->defaultAnswer
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
