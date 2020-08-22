<?php

declare(strict_types=1);

namespace Antidot\Installer;

use Antidot\Installer\Question\ApplicationType;
use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    protected Composer $composer;
    protected IOInterface $io;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }


    /** @return array<string, string> */
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_CREATE_PROJECT_CMD => 'onCreateProject',
        ];
    }

    public function onCreateProject(Event $event): void
    {
        /** @var int $answer */
        $answer = $this->io->select(
            ApplicationType::QUESTION,
            ApplicationType::OPTIONS,
            ApplicationType::WEB_APP
        );

        $this->io->write(ApplicationType::OPTIONS[$answer]);
    }
}
