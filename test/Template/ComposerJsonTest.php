<?php

declare(strict_types=1);


namespace AntidotTest\Installer\Template;

use Antidot\Installer\ApplicationType\MicroAppInstaller;
use Antidot\Installer\Template\ComposerJson;
use Composer\IO\IOInterface;
use Exception;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

use function putenv;


/**
 * @runInSeparateProcess
 */
class ComposerJsonTest extends TestCase
{
    use PHPMock;

    private $io;
    private $exec;

    public function setUp(): void
    {
        $this->io = $this->createMock(IOInterface::class);
    }

    /**
     * @dataProvider getComposerJsonFile
     * It must run in separate process to allow mocking "exec" function.
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItShouldPrepareComposerJsonFile(array $currentFileSystem, array $expectedFileSystem): void
    {
        $exec = $this->getFunctionMock('Antidot\Installer', "exec");
        vfsStream::setup('my-dir', null, $currentFileSystem);
        putenv('COMPOSER=' . vfsStream::url('my-dir') . '/composer.json');
        $exec->expects($this->once());
        $this->io->expects($this->once())
            ->method('ask')
            ->willReturn('Micro');

        $composerJson = new ComposerJson($this->io);
        $composerJson->prepare(vfsStream::url('my-dir') . '/app', MicroAppInstaller::DEPENDENCIES, []);
        $this->assertEquals($expectedFileSystem, vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure());
    }

    public function getComposerJsonFile()
    {
        $original = $this->getDefaultComposerJson();
        return [
            'Micro HTTP App' => [
                [
                    'app' => [],
                    'composer.json' => $original,
                ],
                [
                    'my-dir' => [
                        'app' => [
                            'composer.json' => $this->getMicroComposerJson()
                        ],
                        'composer.json' => $original,
                    ]
                ]
            ]
        ];
    }

    private function getDefaultComposerJson(): string
    {
        return <<<'EOT'
{
    "name": "antidot-fw/skeleton",
    "description": "Antidot Framework skeleton project",
    "keywords": [
        "psr-7",
        "psr-11",
        "psr-15"
    ],
    "type": "project",
    "license": "BSD-2-Clause",
    "authors": [
        {
            "name": "kpicaza"
        }
    ],
    "require": {
        "php": "^7.4.0"
    },
    "require-dev": {
        "antidot-fw/installer": "@dev",
        "laminas/laminas-component-installer": "^2.2",
        "phpunit/phpunit": "^8.0 || ^9.0",
        "roave/security-advisories": "dev-master",
        "squizlabs/php_codesniffer": "^3.4"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "App\\Test\\": "test"
        }
    },
    "scripts": {
        "check-all": [
            "@cs-check",
            "@test"
        ],
        "cs-check": "phpcs src --colors",
        "cs-fix": "phpcbf src --colors",
        "test": "phpunit --colors=always"
    },
    "config": {
        "sort-packages": true
    },
    "extra": {
        "laminas": {
            "component-whitelist": [
                "antidot-fw/framework",
                "antidot-fw/logger",
                "antidot-fw/dbal-adapter",
                "antidot-fw/doctrine",
                "antidot-fw/session",
                "antidot-fw/message-queue",
                "antidot-fw/aura-router-adapter",
                "antidot-fw/cli",
                "antidot-fw/fast-router-adapter",
                "antidot-fw/phug-template-renderer",
                "antidot-fw/twig-template-renderer",
                "antidot-fw/event-dispatcher",
                "antidot-fw/symfony-config-translator",
                "wshafer/psr11-monolog",
                "laminas/laminas-httphandlerrunner",
                "laminas/laminas-diactoros"
            ]
        }
    }
}

EOT;
    }

    private function getMicroComposerJson(): string
    {
        return <<<'EOT'
{
    "keywords": [
        "psr-7",
        "psr-11",
        "psr-15"
    ],
    "type": "project",
    "license": "proprietary",
    "authors": [
        {
            "name": "kpicaza"
        }
    ],
    "require": {
        "php": "^7.4.0",
        "antidot-fw/framework": "^0.1.2",
        "antidot-fw/container": "^0.1.1",
        "antidot-fw/fast-router-adapter": "^0.1.0"
    },
    "require-dev": {
        "laminas/laminas-component-installer": "^2.2",
        "phpunit/phpunit": "^8.0 || ^9.0",
        "roave/security-advisories": "dev-master",
        "squizlabs/php_codesniffer": "^3.4"
    },
    "autoload": {
        "psr-4": {
            "Micro\\": "src"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Micro\\Test\\": "test"
        }
    },
    "scripts": {
        "check-all": [
            "@cs-check",
            "@test"
        ],
        "cs-check": "phpcs src --colors",
        "cs-fix": "phpcbf src --colors",
        "test": "phpunit --colors=always"
    },
    "config": {
        "sort-packages": true
    },
    "extra": {
        "laminas": {
            "component-whitelist": [
                "antidot-fw/framework",
                "antidot-fw/logger",
                "antidot-fw/dbal-adapter",
                "antidot-fw/doctrine",
                "antidot-fw/session",
                "antidot-fw/message-queue",
                "antidot-fw/aura-router-adapter",
                "antidot-fw/cli",
                "antidot-fw/fast-router-adapter",
                "antidot-fw/phug-template-renderer",
                "antidot-fw/twig-template-renderer",
                "antidot-fw/event-dispatcher",
                "antidot-fw/symfony-config-translator",
                "wshafer/psr11-monolog",
                "laminas/laminas-httphandlerrunner",
                "laminas/laminas-diactoros"
            ]
        }
    }
}

EOT;
    }

}
