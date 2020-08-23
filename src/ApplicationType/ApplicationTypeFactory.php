<?php

declare(strict_types=1);

namespace Antidot\Installer\ApplicationType;

use Antidot\Installer\Question\ApplicationTypes;
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
        if (ApplicationTypes::WEB_APP === $applicationType) {
            return new WebAppInstaller($io, $composer, new ComposerJson($io));
        }
        if (ApplicationTypes::MICRO_APP === $applicationType) {
            return new MicroAppInstaller($io, $composer, new ComposerJson($io));
        }

        throw new InvalidArgumentException(sprintf(
            self::INVALID_TYPE_MESSAGE,
            $applicationType
        ));
    }
}
