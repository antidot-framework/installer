<?php

declare(strict_types=1);

namespace Antidot\Installer;

use Antidot\Installer\ApplicationType\ApplicationTypeFactory;
use Antidot\Installer\Question\ApplicationTypes;
use Antidot\Installer\Question\InstallationPath;
use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

use function dirname;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    protected Composer $composer;
    protected IOInterface $io;
    protected InstallationPath $installationPathQuestion;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->installationPathQuestion = new InstallationPath($io);
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
        $package = $this->composer->getPackage();
        if ('antidot-fw/skeleton' !== $package->getName()) {
            return;
        }

        /** @var int $answer */
        $answer = $this->io->select(ApplicationTypes::QUESTION, ApplicationTypes::OPTIONS, ApplicationTypes::WEB_APP);
        $installer = ApplicationTypeFactory::createByApplicationTypeName(
            ApplicationTypes::OPTIONS[$answer],
            $this->io
        );

        $installationPath = $this->installationPathQuestion->ask(
            dirname($this->composer->getInstallationManager()->getInstallPath($package), 3) . '/'
        );

        $installer->install($installationPath);
    }
}
