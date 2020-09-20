Antidot Framework Installer
=================

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/antidot-framework/installer/badges/quality-score.png?b=1.0.x)](https://scrutinizer-ci.com/g/antidot-framework/installer/?branch=1.0.x)
[![Code Coverage](https://scrutinizer-ci.com/g/antidot-framework/installer/badges/coverage.png?b=1.0.x)](https://scrutinizer-ci.com/g/antidot-framework/installer/?branch=1.0.x)
[![Build Status](https://scrutinizer-ci.com/g/antidot-framework/installer/badges/build.png?b=1.0.x)](https://scrutinizer-ci.com/g/antidot-framework/installer/build-status/1.0.x)
[![Total Downloads][ico-downloads]][link-downloads]
[![Maintainability](https://api.codeclimate.com/v1/badges/aaa9bb8ececfaa6276b3/maintainability)](https://codeclimate.com/github/antidot-framework/installer/maintainability)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/antidot-framework/installer/badges/code-intelligence.svg?b=1.0.x)](https://scrutinizer-ci.com/code-intelligence)

Antidot Framework skeleton installer. This package let you choosing between different types of Antidot framework project skeletons:

* Classic Web App
* Serverless App
* Console Line Tool
* Micro Http App
* React Http App

## Requirements

* PHP >= 7.4.0 for current
* Composer

## Micro HTTP App

Lightweight PSR-15 middleware application.

[![asciicast](https://asciinema.org/a/360740.svg)](https://asciinema.org/a/360740)

### Dependencies

* [Antidot Framework](https://github.com/antidot-framework/antidot-framework)
* [Antidot Container](https://github.com/antidot-framework/container)
* [Antidot Fast Router Adapter](https://github.com/antidot-framework/fast-router-adapter)

### Routing

You can add your routes with it custom middlewares in `public/index.php` file, take a look at the example:

```php 
<?php
// public/index.php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
...

    // Application Routes    
    $app->get('/', [
        static function(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
            $request = $request->withAttribute('docs_url', 'https://antidotfw.io');
            return $handler->handle($request);
        },
        static function(ServerRequestInterface $request): ResponseInterface {
            return new JsonResponse([
                'message' => 'Welcome to Antidot Framework Micro HTTP App.',
                'docs' => $request->getAttribute('docs_url'),
            ]);
        }
    ], 'homepage');
...

```

### File structure

```
config/
    config.php
    container.php
    framework.prod.php
public/
    index.php
test/
var/
    cache/
.gitignore
composer.json
phpcs.xml.dist
phpunit.xml.dist
README.md        
```

## Docker environment

[ico-version]: https://img.shields.io/packagist/v/antidot-fw/installer.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-BSD%202--Clause-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/antidot-fw/installer.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/antidot-fw/installer
[link-downloads]: https://packagist.org/packages/antidot-fw/installer
