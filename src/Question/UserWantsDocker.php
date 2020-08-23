<?php

declare(strict_types=1);

namespace Antidot\Installer\Question;

use Composer\IO\IOInterface;

class UserWantsDocker
{

    private IOInterface $io;

    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }

    public function ask(): bool
    {
        return $this->io->askConfirmation(
            'Do you want to install pre-configured Docker environment? [Y/<info>N</info>]',
            false
        );
    }
}
