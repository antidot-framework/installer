<?php

declare(strict_types=1);

namespace Antidot\Installer\Question;

use Composer\IO\IOInterface;
use InvalidArgumentException;
use Throwable;

use function array_key_first;
use function array_keys;
use function file_get_contents;
use function json_decode;
use function preg_match;
use function sprintf;

use const JSON_THROW_ON_ERROR;

class AdditionalPackages
{
    private const PACKAGIST_API = 'https://packagist.org/packages/';
    private IOInterface $io;

    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }

    /**
     * @return array<string, string>
     */
    public function ask(): array
    {
        $packages = [];

        do {
            $installAnotherPackage = $this->io->askConfirmation(
                'Do you want no add any package from packagist? [Y/<info>N</info>]: ',
                false
            );
            try {
                if (true === $installAnotherPackage) {
                    $packageName = $this->io->ask('Add package name "vendor/package": ', null);
                    /** @var string $response */
                    $response = file_get_contents(self::PACKAGIST_API . $packageName . '.json');
                    /** @var array<string, mixed> $packageInfo */
                    $packageInfo = json_decode(
                        $response,
                        true,
                        48,
                        JSON_THROW_ON_ERROR
                    );
                    if (isset($packageInfo['status']) && 'error' === $packageInfo['status']) {
                        throw new InvalidArgumentException('Invalid package name %s given.');
                    }

                    /** @
                     * @var array<string> $versions
                     * @psalm-suppress MixedArrayAccess
                     * @psalm-suppress MixedArgument
                     */
                    $versions = array_keys($packageInfo['package']['versions']);
                    /** @var string $packageVersion */
                    $packageVersion = $this->io->select(
                        sprintf(
                            'Select version for package %s: [<info>%s</info>] ',
                            $packageName,
                            $versions[array_key_first($versions)] ?? '*'
                        ),
                        $versions,
                        $versions[array_key_first($versions)] ?? '*'
                    );

                    if (null !== $packageName) {
                        $prefix = '';
                        if (preg_match('`^(?:v)?\d+\.\d+\.\d+(?:\.\d+)?$`', $packageVersion)) {
                            $prefix = '^';
                        }
                        $packages[$packageName] = $prefix . $versions[$packageVersion];
                    }
                }
            } catch (Throwable $exception) {
                $installAnotherPackage = true;
                $this->io->writeError('<error>' . $exception->getMessage() . '</error>');
            }
        } while (true === $installAnotherPackage);

        return $packages;
    }
}
