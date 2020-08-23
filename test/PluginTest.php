<?php

declare(strict_types=1);


namespace AntidotTest\Installer;

use Antidot\Installer\Plugin;
use Antidot\Installer\Question\ApplicationTypes;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Script\Event;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    private $composer;
    private $io;

    public function setUp(): void
    {
        $this->composer = $this->createMock(Composer::class);
        $this->io = $this->createMock(IOInterface::class);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals([
            'post-create-project-cmd' => 'onCreateProject',
        ], Plugin::getSubscribedEvents());
    }

    public function testItShouldPrepareProjectOnCreateProjectWithAntidotSkeletonPackage(): void
    {
        $event = $this->createMock(Event::class);
        $this->composer->expects($this->once())
            ->method('getPackage')
            ->willReturn($this->createConfiguredMock(Package::class, ['getName' => 'antidot-fw/skeleton']));
        $this->io->expects($this->once())
            ->method('select')
            ->with(ApplicationTypes::QUESTION, ApplicationTypes::OPTIONS, ApplicationTypes::WEB_APP)
            ->willReturn(0);
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
