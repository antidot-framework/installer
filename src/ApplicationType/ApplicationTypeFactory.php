<?php

declare(strict_types=1);

namespace Antidot\Installer\ApplicationType;

use Antidot\Installer\Question\ApplicationTypes;
use Antidot\Installer\RunInstall;
use Antidot\Installer\Template\ComposerJson;
use Composer\Composer;
use Composer\IO\IOInterface;
use InvalidArgumentException;

use function sprintf;

class ApplicationTypeFactory
{
    public const INVALID_TYPE_MESSAGE = 'Invalid application type "%s" selected.';

    public static function createByApplicationTypeName(
        string $applicationType,
        IOInterface $io,
        Composer  $composer
    ): App {
        $composerManipulator = new ComposerJson($io, new RunInstall());

        if (ApplicationTypes::WEB_APP === $applicationType) {
            return new WebAppInstaller($io, $composer, $composerManipulator);
        }
        if (ApplicationTypes::MICRO_APP === $applicationType) {
            return new MicroAppInstaller($io, $composer, $composerManipulator);
        }

        throw new InvalidArgumentException(sprintf(
            self::INVALID_TYPE_MESSAGE,
            $applicationType
        ));
    }
}
