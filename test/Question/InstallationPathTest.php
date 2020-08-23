<?php

declare(strict_types=1);


namespace AntidotTest\Installer\Question;

use Antidot\Installer\Question\InstallationPath;
use Composer\IO\IOInterface;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/** @runInSeparateProcess */
class InstallationPathTest extends TestCase
{
    private $io;

    public function setUp(): void
    {
        $this->io = $this->createMock(IOInterface::class);
    }

    /** @dataProvider getInstallationPath */
    public function testItShouldAskForAnInstallationPath(string $givenPath, string $expectedPad, array $fileStructure): void
    {
        vfsStream::setup('my-dir', null, $fileStructure);
        $this->io->expects($this->once())
            ->method('askConfirmation')
            ->willReturn(true);

        $installationPathQuestion = new InstallationPath($this->io);
        $installationPath = $installationPathQuestion->ask($givenPath);
        $this->assertEquals($installationPath, $expectedPad);
    }

    /** @dataProvider getInstallationPath */
    public function testItShouldAskForACustomInstallationPath(string $givenPath, string $expectedPad, array $fileStructure): void
    {
        vfsStream::setup('my-dir', null, $fileStructure);
        $this->io->expects($this->exactly(2))
            ->method('askConfirmation')
            ->willReturnOnConsecutiveCalls(false, true);
        $this->io->expects($this->once())
            ->method('ask')
            ->willReturn($givenPath);

        $installationPathQuestion = new InstallationPath($this->io);
        $installationPath = $installationPathQuestion->ask('/opt/unwanted/path');
        $this->assertEquals($installationPath, $expectedPad);
    }

    public function getInstallationPath(): array
    {
        $basePath = vfsStream::url('my-dir');

        return [
            'Existing Path ending with "/"' => [
                $basePath . '/some/path/',
                $basePath . '/some/path',
                [
                    'some' => [
                        'path' => [],
                    ],
                ]
            ],
            'Existing Path ending without "/"' => [
                $basePath . '/some/path',
                $basePath . '/some/path',
                [
                    'some' => [
                        'path' => [
                        ],
                    ],
                ]
            ],
            'Missing Path ending with "/"' => [
                $basePath . '/some/path/',
                $basePath . '/some/path',
                []
            ],
            'Missing Path ending without "/"' => [
                $basePath . '/some/path',
                $basePath . '/some/path',
                []
            ],
        ];
    }
}
