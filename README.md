Antidot Framework Installer
=================

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/antidot-framework/installer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/antidot-framework/installer/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/antidot-framework/installer/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/antidot-framework/installer/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/antidot-framework/installer/badges/build.png?b=master)](https://scrutinizer-ci.com/g/antidot-framework/installer/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/antidot-framework/installer/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![Maintainability](https://api.codeclimate.com/v1/badges/aaa9bb8ececfaa6276b3/maintainability)](https://codeclimate.com/github/antidot-framework/installer/maintainability)

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

### Dependencies

* [Antidot Framework]()
* [Antidot Container]()
* [Antidot Fast Router Adapter]()


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


