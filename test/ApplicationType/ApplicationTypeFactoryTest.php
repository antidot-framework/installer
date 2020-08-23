<?php

declare(strict_types=1);


namespace AntidotTest\Installer\ApplicationType;

use Antidot\Installer\ApplicationType\ApplicationTypeFactory;
use Antidot\Installer\ApplicationType\MicroAppInstaller;
use Antidot\Installer\ApplicationType\WebAppInstaller;
use Antidot\Installer\Question\ApplicationTypes;
use Composer\Composer;
use Composer\IO\IOInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ApplicationTypeFactoryTest extends TestCase
{
    private $composer;
    private $io;

    public function setUp(): void
    {
        $this->composer = $this->createMock(Composer::class);
        $this->io = $this->createMock(IOInterface::class);
    }

    /** @dataProvider getApplicationTypesByName */
    public function testCreateByApplicationTypeName(string $applicationTypeName, string $installerType): void
    {
        $applicationTypeInstaller = ApplicationTypeFactory::createByApplicationTypeName(
            $applicationTypeName,
            $this->io,
            $this->composer
        );
        $this->assertInstanceOf($installerType, $applicationTypeInstaller);
    }

    /** @dataProvider getUnsupportedApplicationTypesByName */
    public function testItShouldThrowAnExceptionWithNotSupportedApplicationType(string $applicationTypeName): void
    {
        $this->expectException(InvalidArgumentException::class);
        ApplicationTypeFactory::createByApplicationTypeName(
            $applicationTypeName,
            $this->io,
            $this->composer
        );
    }

    public function getApplicationTypesByName(): array
    {
        return [
            'Classic Web App' => [
                ApplicationTypes::WEB_APP,
                WebAppInstaller::class,
            ],
            'Micro HTTP App' => [
                ApplicationTypes::MICRO_APP,
                MicroAppInstaller::class,
            ],
        ];
    }

    public function getUnsupportedApplicationTypesByName(): array
    {
        return [
            'Symfony Application' => [
                'Symfony Application',
            ],
            'Laravel Application' => [
                'Laravel Application',
            ],
        ];
    }
}
