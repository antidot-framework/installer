<?php

declare(strict_types=1);


namespace AntidotTest\Installer;

use Antidot\Installer\Plugin;
use Antidot\Installer\Question\ApplicationTypes;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Script\Event;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    private $composer;
    private $io;

    protected function setUp(): void
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

    public function testOnCreateProject(): void
    {
        $event = $this->createMock(Event::class);
        $this->io->expects($this->once())
            ->method('select')
            ->with(ApplicationTypes::QUESTION, ApplicationTypes::OPTIONS, ApplicationTypes::WEB_APP)
            ->willReturn(0);
        $plugin = new Plugin();
        $plugin->activate($this->composer, $this->io);
        $plugin->onCreateProject($event);
    }
}
