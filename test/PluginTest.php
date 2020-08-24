<?php

declare(strict_types=1);


namespace AntidotTest\Installer;

use Antidot\Installer\Plugin;
use Antidot\Installer\Question\ApplicationTypes;
use Composer\Composer;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Script\Event;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    private $composer;
    private $io;
    private $installationManager;

    public function setUp(): void
    {
        $this->composer = $this->createMock(Composer::class);
        $this->io = $this->createMock(IOInterface::class);
        $this->installationManager = $this->createMock(InstallationManager::class);
        vfsStream::setup(
            'my-dir',
            null,
            [
                'vendor' => [
                    'antidot-fw' => [
                        'skeleton' => []
                    ],
                ],
                'CHANGELOG.md' => '',
                'CODE_OF_CONDUCT.md' => '',
                'composer.json' => '',
                'CONTRIBUTING.md' => '',
                'PULL_REQUEST_TEMPLATE.md' => '',
                'LICENSE' => '',
            ]
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals([
            'post-create-project-cmd' => 'onCreateProject',
        ], Plugin::getSubscribedEvents());
    }

    public function testItShouldPrepareProjectOnCreateProjectWithAntidotSkeletonPackage(): void
    {
        $basePath = vfsStream::url('my-dir');
        $installPath = $basePath . '/vendor/antidot-fw/skeleton';
        $event = $this->createMock(Event::class);
        $package = $this->createConfiguredMock(Package::class, ['getName' => 'antidot-fw/skeleton']);
        $this->installationManager->expects($this->once())
            ->method('getInstallPath')
            ->with($package)
            ->willReturn($installPath);
        $this->composer->expects($this->once())
            ->method('getPackage')
            ->willReturn($package);
        $this->composer->expects($this->once())
            ->method('getInstallationManager')
            ->willReturn($this->installationManager);
        $this->io->expects($this->once())
            ->method('select')
            ->with(ApplicationTypes::QUESTION, ApplicationTypes::OPTIONS, ApplicationTypes::WEB_APP)
            ->willReturn(0);
        $this->io->expects($this->exactly(2))
            ->method('askConfirmation')
            ->willReturnOnConsecutiveCalls(false, true);
        $this->io->expects($this->once())
            ->method('ask')
            ->willReturn($installPath);

        $plugin = new Plugin();
        $plugin->activate($this->composer, $this->io);
        $plugin->onCreateProject($event);
    }

    public function testItShouldDoNothingOnCreateProjectWithNoAntidotSkeletonPackage(): void
    {
        $event = $this->createMock(Event::class);
        $this->composer->expects($this->once())
            ->method('getPackage')
            ->willReturn($this->createConfiguredMock(Package::class, ['getName' => 'antidot-fw/foo']));
        $this->io->expects($this->exactly(0))
            ->method('select');
        $plugin = new Plugin();
        $plugin->activate($this->composer, $this->io);
        $plugin->onCreateProject($event);
    }
}
