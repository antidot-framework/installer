<?php

declare(strict_types=1);

namespace Antidot\Installer\Template\Web;

use Antidot\Installer\Template\CommonFileStructure;

class FileStructure extends CommonFileStructure
{
    private const FILES = [
        'getGitignore' => '.gitignore',
        'getConfig' => 'config/config.php',
        'getCliConfig' => 'config/cli-config.php',
        'getConsole' => 'bin/console',
        'getFrameworkConfig' => 'config/services/framework.prod.php',
        'getDevelopmentConfig' => 'config/services/framework.dev.php.dist',
        'getContainer' => 'config/container.php',
        'getCliContainer' => 'config/cli-container.php',
        'getRoutes' => 'router/routes.php',
        'getMiddleware' => 'router/middleware.php',
        'getIndex' => 'public/index.php',
        'getHome' => 'src/Handler/Home.php',
        'getReadme' => 'README.md',
    ];

    private const DIRECTORIES = [
        'public',
        'bin',
        'config/services',
        'var/cache',
        'var/log',
        'test',
        'src/Handler',
        'router',
    ];

    public function create(string $installationPath): void
    {
        $this->verifyInstallationPath($installationPath);
        $this->createDirectories($installationPath, self::DIRECTORIES);
        $this->createFiles($installationPath, self::FILES);
        $this->removeCommunityFiles($installationPath);
    }

    public static function getGitignore(): string
    {
        $gitignoreContents = <<<EOT
/composer.lock

/config/services/*.dev.php
/config/services/*.local.php

/vendor
/var/cache/*
!/var/cache/.gitkeep
/var/log/*
!/var/log/.gitkeep

EOT;

        return $gitignoreContents;
    }

    public static function getConfig(): string
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
    //   - `services/*.php`
    //   - `services/*.local.php`
    //   - `services/*.dev.php`
    new PhpFileProvider(realpath(__DIR__).'/services/{{,*.}prod,{,*.}local,{,*.}dev}.php'),
    new ArrayProvider($cacheConfig),
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();

PHP;

        return $configContent;
    }

    public static function getCliConfig(): string
    {
        $configContent = <<<'PHP'
<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;

$config = require __DIR__ . '/config.php';
$cliConfig['services'] = $config['console']['services'] ?? [];
$cliConfig['factories'] = $config['console']['factories'] ?? [];
$cacheConfig = [
    'cli_config_cache_path' => 'var/cache/cli-config-cache.php',
];

return (new ConfigAggregator([
    new ArrayProvider($config),
    new ArrayProvider($cliConfig),
    new ArrayProvider($cacheConfig),
], $cacheConfig['cli_config_cache_path']))->getMergedConfig();

PHP;

        return $configContent;
    }

    public static function getConsole(): string
    {
        $consoleContent = <<<'BASH'
#!/usr/bin/env php
<?php

declare(strict_types=1);

use Antidot\Cli\Application\Console;

set_time_limit(0);

call_user_func(static function (): void {
    require __DIR__.'/../vendor/autoload.php';
    $container = require __DIR__.'/../config/cli-container.php';
    $console = $container->get(Console::class);

    $console->run();
});

BASH;

        return $consoleContent;
    }

    public static function getFrameworkConfig(): string
    {
        $frameworkConfigContents = <<<'PHP'
<?php

declare(strict_types=1);

use App\Handler\Home;
use Monolog\Logger;

return [
    'debug' => false,
    'config_cache_enabled' => true,
    'monolog' => [
        'handlers' => [
            'default' => [
                'type' => 'stream',
                'options' => [
                    'stream' => sprintf('var/log/%s.log', (new DateTimeImmutable())->format('Y-m-d')),
                    'level' => Logger::ERROR,
                ],
            ],
        ],  
    ],
    'services' => [
        Home::class => Home::class,
    ],
];

PHP;

        return $frameworkConfigContents;
    }

    public static function getDevelopmentConfig(): string
    {
        $frameworkConfigContents = <<<'PHP'
<?php

declare(strict_types=1);

use Monolog\Logger;

return [
    'debug' => true,
    'config_cache_enabled' => false,
    'monolog' => [
        'handlers' => [
            'default' => [
                'options' => [
                    'level' => Logger::DEBUG,
                ],
            ],
        ],  
    ],
];

PHP;

        return $frameworkConfigContents;
    }

    public static function getCliContainer(): string
    {
        $containerContent = <<<'PHP'
<?php

// Load configuration
use Antidot\Container\Builder;

$config = require __DIR__ . '/../config/cli-config.php';

return Builder::build($config, true);

PHP;

        return $containerContent;
    }

    public static function getIndex(): string
    {
        $indexContent = <<<'PHP'
<?php

declare(strict_types=1);

// Delegate static file requests back to the PHP built-in webserver
use Antidot\Application\Http\Application;

if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
    return false;
}
\chdir(\dirname(__DIR__));
require 'vendor/autoload.php';
/**
 * Self-called anonymous function that creates its own scope and keep the global namespace clean.
 */
\call_user_func(static function (): void {
    error_reporting(E_ALL & ~E_USER_DEPRECATED & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);

    /** @var \Psr\Container\ContainerInterface $container */
    $container = require 'config/container.php';
    /** @var Application $app */
    $app = $container->get(Application::class);
    // Execute programmatic/declarative middleware pipeline and routing
    // configuration statements
    (require 'router/middleware.php')($app, $container);
    (require 'router/routes.php')($app, $container);
    $app->run();
});

PHP;

        return $indexContent;
    }

    public static function getRoutes(): string
    {
        $routesContent = <<<'PHP'
<?php

declare(strict_types=1);

use Antidot\Application\Http\Application;
use App\Handler\Home;
use Psr\Container\ContainerInterface;

/**
 * Setup routes with a single request method and routed Middleware:
 *
 * $app->get('/', [App\Middleware\HomePageMiddleware::class, App\Handler\HomePageHandler::class], 'home');
 * $app->post('/album', [
 *      App\Middleware\HomePageMiddleware::class, 
 *      App\Handler\AlbumCreateHandler::class
 * ], 'album.create');
 * $app->put('/album/:id', [App\Handler\AlbumUpdateHandler::class], 'album.put');
 * $app->patch('/album/:id', [App\Handler\AlbumUpdateHandler::class,] 'album.patch');
 * $app->delete('/album/:id', [App\Handler\AlbumDeleteHandler::class], 'album.delete');
 */
return static function (Application $app, ContainerInterface $container) : void {
    $app->get('/', [Home::class], 'home');
};

PHP;

        return $routesContent;
    }

    public static function getMiddleware(): string
    {
        $middlewareContent = <<<'PHP'
<?php

declare(strict_types=1);

use Antidot\Application\Http\Application;
use Antidot\Application\Http\Middleware\ErrorMiddleware;
use Antidot\Application\Http\Middleware\RouteDispatcherMiddleware;
use Antidot\Application\Http\Middleware\RouteNotFoundMiddleware;
use Antidot\Logger\Application\Http\Middleware\ExceptionLoggerMiddleware;
use Antidot\Logger\Application\Http\Middleware\RequestLoggerMiddleware;

return static function (Application $app) : void {
    $app->pipe(ErrorMiddleware::class);
    $app->pipe(ExceptionLoggerMiddleware::class);
    $app->pipe(RequestLoggerMiddleware::class);
    $app->pipe(RouteDispatcherMiddleware::class);
    $app->pipe(RouteNotFoundMiddleware::class);
};

PHP;

        return $middlewareContent;
    }

    public static function getHome(): string
    {
        $handlerContent = <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;

class Home implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse([
            'docs' => 'https://antidotfw.io',
            'Message' => 'Welcome to Antidot Framework Starter'
        ]);
    }
}

PHP;

        return $handlerContent;
    }

    public static function getReadme(): string
    {
        $readmeContents = <<<'EOT'
# Antidot Framework Web HTTP App

Full featured PSR-15 middleware application.

## Routing

You can add your routes with its custom middlewares in `router/routes.php` file, take a look at the example:

```php 
<?php

declare(strict_types=1);

use Antidot\Application\Http\Application;
use App\Application\Http\Home;
use Psr\Container\ContainerInterface;

return static function (Application $app, ContainerInterface $container) : void {
    $app->get('/', [Home::class], 'home');
    ...
};

```

You can modify global middleware in `router/middleware.php` file, take a look at the example:

```php 
<?php

declare(strict_types=1);

use Antidot\Application\Http\Application;
use Antidot\Application\Http\Middleware\ErrorMiddleware;
use Antidot\Application\Http\Middleware\RouteDispatcherMiddleware;
use Antidot\Application\Http\Middleware\RouteNotFoundMiddleware;
use Antidot\Logger\Application\Http\Middleware\ExceptionLoggerMiddleware;
use Antidot\Logger\Application\Http\Middleware\RequestLoggerMiddleware;

return static function (Application $app) : void {
    $app->pipe(ErrorMiddleware::class);
    $app->pipe(ExceptionLoggerMiddleware::class);
    $app->pipe(RequestLoggerMiddleware::class);
    $app->pipe(RouteDispatcherMiddleware::class);
    $app->pipe(RouteNotFoundMiddleware::class);
};

```

## File structure

```
config/
    services/
        framework.prod.php    
        framework.dev.php.dist    
    config.php
    container.php
    cli-config.php
    cli-container.php
public/
    index.php
router/
    middleware.php
    routes.php
src/
    Handler/
        Home.php
test/
var/
    cache/
.gitignore
composer.json
phpcs.xml.dist
phpunit.xml.dist
README.md        
```

EOT;

        return $readmeContents;
    }
}
