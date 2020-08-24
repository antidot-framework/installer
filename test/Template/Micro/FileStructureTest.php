<?php

declare(strict_types=1);


namespace AntidotTest\Installer\Template\Micro;

use Antidot\Installer\Template\CommonFileStructure;
use Antidot\Installer\Template\Micro\FileStructure;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function sprintf;

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

    /** @dataProvider getWriteDirectories */
    public function testItShouldThrowAnExceptionWithFileSystemPermissionIssues(
        string $rootDir,
        int $permissions,
        string $installPath,
        string $exceptionMessage
    ): void {
        vfsStream::setup($rootDir, $permissions);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf($exceptionMessage, vfsStream::url($installPath)));
        $fileStructure = new FileStructure();
        $fileStructure->create(vfsStream::url($installPath));
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

    public function getWriteDirectories()
    {
        return [
            'Existing Not writable dir' => [
                'my-dir',
                0444,
                'my-dir',
                CommonFileStructure::NOT_WRITABLE_MESSAGE,
            ],
            'Non Existing Not writable dir' => [
                'my-dir',
                0444,
                'my-dir/some/path',
                CommonFileStructure::NOT_PERMISSIONS_MESSAGE,
            ],
        ];
    }
}
