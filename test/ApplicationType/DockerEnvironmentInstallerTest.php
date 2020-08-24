<?php

declare(strict_types=1);


namespace AntidotTest\Installer\ApplicationType;

use Antidot\Installer\ApplicationType\DockerEnvironmentInstaller;
use Antidot\Installer\Template\Docker\FileStructure;
use Composer\IO\IOInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DockerEnvironmentInstallerTest extends TestCase
{
    /** @var IOInterface|MockObject */
    private $io;

    public function setUp(): void
    {
        $this->io = $this->createMock(IOInterface::class);
    }

    /** @dataProvider getExpectedStructure */
    public function testItShouldInstallDockerEnvironment(array $expectedStructure): void
    {
        vfsStream::setup('my-dir');
        $this->io->expects($this->once())
            ->method('askConfirmation')
            ->willReturn(true);

        $installer = new DockerEnvironmentInstaller($this->io);
        $installer->install(vfsStream::url('my-dir'));
        $this->assertEquals($expectedStructure, vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure());
    }

    public function getExpectedStructure(): array
    {
        return [
            'Docker Environment File Structure' => [
                [
                    'my-dir' => [
                        'docker' => [
                            'php' => [
                                'conf.d' => [
                                    'redis.ini' => FileStructure::getRedisIni(),
                                ],
                                'Dockerfile' => FileStructure::getPhpDockerfile(),
                            ],
                            'nginx' => [
                                'Dockerfile' => FileStructure::getNginxDockerfile(),
                                'nginx.conf' => FileStructure::getNginxConf(),
                                'default.conf' => FileStructure::getNginxDefaultConf(),
                            ],
                        ],
                        'docker-compose.yml' => FileStructure::getDockerCompose(),
                    ]
                ]
            ]
        ];
    }
}
