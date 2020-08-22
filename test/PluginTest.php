<?php

declare(strict_types=1);


namespace AntidotTest\Installer;

use Antidot\Installer\Plugin;
use Antidot\Installer\Question\ApplicationType;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    private $composer;
    private $io;
    private $plugin;

    public function setUp(): void
    {
        $this->composer = $this->createMock(Composer::class);
        $this->io = $this->createMock(IOInterface::class);
        $this->plugin = new Plugin();
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
            ->with(ApplicationType::QUESTION, ApplicationType::OPTIONS, ApplicationType::WEB_APP)
            ->willReturn(0);
        $this->plugin->activate($this->composer, $this->io);
        $this->plugin->onCreateProject($event);
    }
}
