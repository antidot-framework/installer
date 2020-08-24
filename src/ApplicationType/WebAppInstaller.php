<?php

declare(strict_types=1);

namespace Antidot\Installer\ApplicationType;

use Antidot\Installer\Template\ComposerJson;
use Composer\Composer;
use Composer\IO\IOInterface;

class WebAppInstaller implements App
{
    /** @var IOInterface */
    private IOInterface $io;
    /** @var ComposerJson */
    private ComposerJson $manipulator;

    public function __construct(IOInterface $io, ComposerJson $manipulator)
    {
        $this->io = $io;
        $this->manipulator = $manipulator;
    }

    public function install(string $installationPath): void
    {
        // TODO: Implement install() method.
    }
}
