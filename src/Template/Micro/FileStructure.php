<?php

declare(strict_types=1);

namespace Antidot\Installer\Template\Micro;

use RuntimeException;

use function file_put_contents;
use function is_dir;
use function mkdir;
use function sprintf;

class FileStructure
{
    private const FILES = [
        'getGitignore' => '.gitignore',
        'getConfig' => 'config/config.php',
        'getFrameworkConfig' => 'config/framework.prod.php',
        'getContainer' => 'config/container.php',
        'getIndex' => 'public/index.php',
        'getReadme' => 'README.md',
    ];

    private const DIRECTORIES = [
        'public',
        'config',
        'var',
        'var/cache',
    ];

    public function create(string $installationPath): void
    {
        foreach (self::DIRECTORIES as $directory) {
            if (!mkdir($dir = sprintf('%s/%s', $installationPath, $directory)) && !is_dir($dir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }

        foreach (self::FILES as $method => $filename) {
            file_put_contents(sprintf('%s/%s', $installationPath, $filename), $this->$method());
        }
    }

    private function getGitignore(): string
    {
        $gitignoreContents = <<<EOT
/composer.lock

/config/*.dev.php
/config/*.local.php
/config/development.config.php

/vendor
/var/cache/*
!/var/cache/.gitkeep
/var/log/*
!/var/log/.gitkeep
EOT;

        return $gitignoreContents;
    }

    private function getConfig(): string
    {
        $configContent = <<<'PHP'
<?php

declare(strict_types=1);

use Antidot\DevTools\Container\Config\ConfigProvider as DevToolsConfigProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\PhpFileProvider;

// To enable or disable caching, set the `ConfigAggregator::ENABLE_CACHE` boolean in
// `config/autoload/local.php`.
$cacheConfig = [
    'config_cache_path' => 'var/cache/config-cache.php',
];

$aggregator = new ConfigAggregator([
    class_exists(DevToolsConfigProvider::class) ? DevToolsConfigProvider::class : fn() => [],
    // Load application config in a pre-defined order in such a way that local settings
    // overwrite global settings. (Loaded as first to last):
    //   - `*.php`
    //   - `*.global.php`
    //   - `*.local.php`
    //   - `*.dev.php`
    new PhpFileProvider(realpath(__DIR__).'/{{,*.}prod,{,*.}local,{,*.}dev}.php'),
    new ArrayProvider($cacheConfig),
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();

PHP;

        return $configContent;
    }

    private function getContainer(): string
    {
        $containerContent = <<<'PHP'
<?php

declare(strict_types=1);

// Load configuration
use Antidot\Container\Builder;

$config = require __DIR__ . '/../config/config.php';

return Builder::build($config, true);

PHP;

        return $containerContent;
    }

    private function getIndex(): string
    {
        $indexContent = <<<'PHP'
<?php

declare(strict_types=1);

use Antidot\Application\Http\Application;
use Antidot\Application\Http\Middleware\ErrorMiddleware;
use Antidot\Application\Http\Middleware\RouteDispatcherMiddleware;
use Antidot\Application\Http\Middleware\RouteNotFoundMiddleware;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
    return false;
}
\chdir(\dirname(__DIR__));
require 'vendor/autoload.php';
/**
 * Self-called anonymous function that creates its own scope and keep the global namespace clean.
 */
\call_user_func(static function (): void {
    \error_reporting(E_ALL & ~E_USER_DEPRECATED & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);

    /** @var \Psr\Container\ContainerInterface $container */
    $container = require 'config/container.php';
    /** @var Application $app */
    $app = $container->get(Application::class);
    
    // Global Pipeline Configuration
    $app->pipe(ErrorMiddleware::class);
    $app->pipe(RouteDispatcherMiddleware::class);
    $app->pipe(RouteNotFoundMiddleware::class);

    // Application Routes    
    $app->get('/', [
        static function(ServerRequestInterface $request): ResponseInterface {
            return new JsonResponse([
                'message' => 'Welcome to antidot Framework Micro HTTP App.',
                'server_params' => $request->getServerParams(),
            ]);
        }
    ], 'homepage');

    $app->run();
});

PHP;

        return $indexContent;
    }

    private function getReadme(): string
    {
        $readmeContents = <<<EOT
# Antidot Framework Micro HTTP App

Lightweight PSR-15 middleware application.

EOT;

        return $readmeContents;
    }

    private function getFrameworkConfig(): string
    {
        $frameworkConfigContents = <<<'PHP'
<?php

declare(strict_types=1);

return [
    'debug' => false,
    'config_cache_enabled' => true
];

PHP;

        return $frameworkConfigContents;
    }
}
