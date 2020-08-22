<?php

declare(strict_types=1);

namespace Antidot\Installer\ApplicationType;

use Composer\IO\IOInterface;

interface App
{
    public function install(IOInterface $io): void;
}
