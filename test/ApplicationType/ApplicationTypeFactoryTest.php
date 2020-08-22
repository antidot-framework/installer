<?php

declare(strict_types=1);


namespace AntidotTest\Installer\ApplicationType;

use Antidot\Installer\ApplicationType\ApplicationTypeFactory;
use Antidot\Installer\ApplicationType\MicroAppInstaller;
use Antidot\Installer\ApplicationType\WebAppInstaller;
use Antidot\Installer\Question\ApplicationTypes;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ApplicationTypeFactoryTest extends TestCase
{
    /** @dataProvider getApplicationTypesByName */
    public function testCreateByApplicationTypeName(string $applicationTypeName, string $installerType): void
    {
        $applicationTypeInstaller = ApplicationTypeFactory::createByApplicationTypeName($applicationTypeName);
        $this->assertInstanceOf($installerType, $applicationTypeInstaller);
    }

    /** @dataProvider getUnsupportedApplicationTypesByName */
    public function testItShouldThrowAnExceptionWithNotSupportedApplicationType(string $applicationTypeName): void
    {
        $this->expectException(InvalidArgumentException::class);
        ApplicationTypeFactory::createByApplicationTypeName($applicationTypeName);
    }

    public function getApplicationTypesByName(): array
    {
        return [
            [
                ApplicationTypes::WEB_APP,
                WebAppInstaller::class,
            ],
            [
                ApplicationTypes::MICRO_APP,
                MicroAppInstaller::class,
            ],
        ];
    }

    public function getUnsupportedApplicationTypesByName(): array
    {
        return [
            [
                'Symfony Application',
            ],
            [
                'Laravel Application',
            ],
        ];
    }
}
