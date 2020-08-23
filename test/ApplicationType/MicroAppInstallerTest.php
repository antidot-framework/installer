<?php

declare(strict_types=1);


namespace AntidotTest\Installer\ApplicationType;

use Antidot\Installer\ApplicationType\MicroAppInstaller;
use Antidot\Installer\Template\ComposerJson;
use Antidot\Installer\Template\Micro\FileStructure;
use Composer\Composer;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use PHPUnit\Framework\TestCase;

class MicroAppInstallerTest extends TestCase
{
    private $composer;
    private $io;
    private $fs;
    private $installationManager;
    private $manipulator;

    public function setUp(): void
    {
        $this->composer = $this->createMock(Composer::class);
        $this->io = $this->createMock(IOInterface::class);
        $this->installationManager = $this->createMock(InstallationManager::class);
        $this->fs = vfsStream::setup(
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
        $this->manipulator = $this->createMock(ComposerJson::class);
    }

    /** @dataProvider getExpectedStructure */
    public function testItShouldInstallMicroAppSkeleton(array $expectedStructure): void
    {
        $basePath = vfsStream::url('my-dir');
        $installPath = $basePath . '/vendor/antidot-fw/skeleton';
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

        $installer = new MicroAppInstaller($this->io, $this->composer, $this->manipulator);
        $installer->install();

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
