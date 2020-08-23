<?php

declare(strict_types=1);


namespace AntidotTest\Installer\Template\Micro;

use Antidot\Installer\Template\Micro\FileStructure;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FileStructureTest extends TestCase
{
    /** @dataProvider getExpectedStructure */
    public function testItShouldCreateMicroAppFileStructure(array $expectedStructure): void
    {
        vfsStream::setup('my-dir');
        $fileStructure = new FileStructure();
        $fileStructure->create(vfsStream::url('my-dir'));
        $this->assertEquals($expectedStructure, vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure());
    }

    public function testItShouldThrowAnExceptionWithFileSystemPermissionIssues(): void
    {
        $this->expectException(RuntimeException::class);
        vfsStream::setup('my-dir', 0444);
        $fileStructure = new FileStructure();
        $fileStructure->create(vfsStream::url('my-dir'));
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
                        '.gitignore' => FileStructure::getGitignore(),
                        'README.md' => FileStructure::getReadme(),
                    ]
                ],
            ]
        ];
    }
}
