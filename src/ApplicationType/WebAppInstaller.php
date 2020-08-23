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
    /** @var Composer */
    private Composer $composer;
    /** @var ComposerJson */
    private ComposerJson $manipulator;

    public function __construct(IOInterface $io, Composer $composer, ComposerJson $manipulator)
    {
        $this->io = $io;
        $this->composer = $composer;
        $this->manipulator = $manipulator;
    }

    public function install(): void
    {
        // TODO: Implement install() method.
    }
}
