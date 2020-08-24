<?php

declare(strict_types=1);


namespace AntidotTest\Installer\Template\Docker;

use Antidot\Installer\Template\Docker\FileStructure;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use PHPUnit\Framework\TestCase;

class FileStructureTest extends TestCase
{
    /** @dataProvider getExpectedStructure */
    public function testItShouldCreateDockerEnvironmentFileStructure(array $expectedStructure): void
    {
        vfsStream::setup('my-dir');
        $fileStructure = new FileStructure();
        $fileStructure->create(vfsStream::url('my-dir'));
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
