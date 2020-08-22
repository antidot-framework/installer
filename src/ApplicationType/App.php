<?php

declare(strict_types=1);

namespace Antidot\Installer\ApplicationType;

use Composer\Composer;
use Composer\IO\IOInterface;

interface App
{
    public function install(IOInterface $io, Composer $composer): void;
}
