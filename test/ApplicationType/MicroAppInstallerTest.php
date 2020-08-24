<?php

declare(strict_types=1);


namespace AntidotTest\Installer\ApplicationType;

use Antidot\Installer\ApplicationType\MicroAppInstaller;
use Antidot\Installer\Template\ComposerJson;
use Antidot\Installer\Template\Micro\FileStructure;
use Composer\IO\IOInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use PHPUnit\Framework\TestCase;

class MicroAppInstallerTest extends TestCase
{
    private $io;
    private $manipulator;

    public function setUp(): void
    {
        $this->io = $this->createMock(IOInterface::class);
        $this->manipulator = $this->createMock(ComposerJson::class);
    }

    /** @dataProvider getExpectedStructure */
    public function testItShouldInstallMicroAppSkeleton(array $expectedStructure): void
    {
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
        $installPath = vfsStream::url('my-dir');
        $this->io->expects($this->once())
            ->method('askConfirmation')
            ->willReturn( false);

        $installer = new MicroAppInstaller($this->io, $this->manipulator);
        $installer->install($installPath);

        $this->assertEquals($expectedStructure, vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure());
    }

    public function getExpectedStructure(): array
    {
        return [
            'Micro Http App File Structure' => [
                [
                    'my-dir' => [
                        'config' => [
                            'config.php' => FileStructure::getConfig(),
                            'container.php' => FileStructure::getContainer(),
                            'framework.prod.php' => FileStructure::getFrameworkConfig(),
                        ],
                        'public' => [
                            'index.php' => FileStructure::getIndex(),
                        ],
                        'test' => [],
                        'var' => [
                            'cache' => [],
                        ],
                        'vendor' => [
                            'antidot-fw' => [
                                'skeleton' => []
                            ],
                        ],
                        '.gitignore' => FileStructure::getGitignore(),
                        'composer.json' => '',
                        'README.md' => FileStructure::getReadme(),
                    ]
                ],
            ]
        ];
    }
}
