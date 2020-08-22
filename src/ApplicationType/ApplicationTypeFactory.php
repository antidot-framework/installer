<?php

declare(strict_types=1);

namespace Antidot\Installer\ApplicationType;

use Antidot\Installer\Question\ApplicationTypes;
use InvalidArgumentException;

use function sprintf;

class ApplicationTypeFactory
{
    public const INVALID_TYPE_MESSAGE = 'Invalid application type "%s" selected.';

    public static function createByApplicationTypeName(string $applicationType): App
    {
        if (ApplicationTypes::WEB_APP === $applicationType) {
            return new WebAppInstaller();
        }
        if (ApplicationTypes::MICRO_APP === $applicationType) {
            return new MicroAppInstaller();
        }

        throw new InvalidArgumentException(sprintf(
            self::INVALID_TYPE_MESSAGE,
            $applicationType
        ));
    }
}
