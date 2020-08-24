<?php

declare(strict_types=1);

namespace Antidot\Installer\ApplicationType;

use Antidot\Installer\Question\ApplicationTypes;
use Antidot\Installer\RunInstall;
use Antidot\Installer\Template\ComposerJson;
use Composer\IO\IOInterface;
use InvalidArgumentException;

use function sprintf;

class ApplicationTypeFactory
{
    public const INVALID_TYPE_MESSAGE = 'Invalid application type "%s" selected.';

    public static function createByApplicationTypeName(
        string $applicationType,
        IOInterface $io
    ): App {
        $composerManipulator = new ComposerJson($io, new RunInstall());

        if (ApplicationTypes::WEB_APP === $applicationType) {
            return new WebAppInstaller($io, $composerManipulator);
        }
        if (ApplicationTypes::MICRO_APP === $applicationType) {
            return new MicroAppInstaller($io, $composerManipulator);
        }

        throw new InvalidArgumentException(sprintf(
            self::INVALID_TYPE_MESSAGE,
            $applicationType
        ));
    }
}
