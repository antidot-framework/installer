<?php

declare(strict_types=1);

namespace Antidot\Installer\Template\Docker;

use Antidot\Installer\Template\CommonFileStructure;

class FileStructure extends CommonFileStructure
{
    private const FILES = [
        'getDockerCompose' => 'docker-compose.yml',
        'getPhpDockerfile' => 'docker/php/Dockerfile',
        'getRedisIni' => 'docker/php/conf.d/redis.ini',
        'getNginxDockerfile' => 'docker/nginx/Dockerfile',
        'getNginxDefaultConf' => 'docker/nginx/default.conf',
        'getNginxConf' => 'docker/nginx/nginx.conf',
    ];

    private const DIRECTORIES = [
        'docker/php/conf.d',
        'docker/nginx',
    ];

    public function create(string $installationPath): void
    {
        $this->verifyInstallationPath($installationPath);
        $this->createDirectories($installationPath, self::DIRECTORIES);
        $this->createFiles($installationPath, self::FILES);
    }

    public static function getDockerCompose(): string
    {
        $dockerComposeContents = <<<YAML
version: "3"

volumes:
  redis:
  app:

services:

  nginx:
    build:
      context: ./docker/nginx/
      args:
        - UID=1000
    ports:
      - 80:80
      - 443:443
    links:
      - redis
      - php
    depends_on:
      - php
    volumes:
      - redis:/var/lib/redis
      - app:/opt/app

  php:
    build:
      context: ./docker/php/
      args:
        - UID=1000
    expose:
      - 9000
    links:
      - redis
    volumes:
      - redis:/var/lib/redis
      - ./:/opt/app
    working_dir: /opt

  redis:
    image: redis:latest
    volumes:
      - redis:/var/lib/redis

YAML;

        return $dockerComposeContents;
    }

    public static function getPhpDockerfile(): string
    {
        $phpDockerfileContents = <<<'EOT'
FROM php:7.4.9-fpm

ARG UID=1000

RUN apt-get update && apt-get install -y git zip zlib1g-dev libicu-dev g++ libxml2-dev \
autoconf pkg-config libssh-dev libonig-dev

RUN docker-php-ext-install pdo_mysql bcmath iconv pcntl mbstring intl calendar sockets

RUN pecl install -o -f redis \
&& rm -rf /tmp/pear \
&& docker-php-ext-enable redis 

COPY ./conf.d/redis.ini $PHP_INI_DIR/conf.d/redis.ini

RUN usermod -u ${UID} www-data

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer global require hirak/prestissimo

EOT;

        return $phpDockerfileContents;
    }

    public static function getRedisIni(): string
    {
        $redisIniContents = <<<INI
session.save_handler=redis
session.save_path="tcp://redis/"

INI;

        return $redisIniContents;
    }

    public static function getNginxDockerfile(): string
    {
        $nginxDockerfileContents = <<<'EOT'
FROM nginx:latest

ARG UID=1000

RUN apt update && apt install openssl -y
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048  -keyout /etc/nginx/nginx.key -out /etc/nginx/nginx.crt \
-subj "/C=GB/ST=London/L=London/O=Global Security/OU=IT Department/CN=example.com"

COPY ./nginx.conf /etc/nginx/nginx.conf
COPY ./default.conf /etc/nginx/conf.d/default.conf

RUN usermod -u ${UID} www-data

EOT;

        return $nginxDockerfileContents;
    }

    public static function getNginxDefaultConf(): string
    {
        $nginxDefaultConfContents = <<<'EOT'
upstream backend {
    least_conn;
    server  php:9000;
}

upstream app_server {
    least_conn;
    server 127.0.0.1:80;
}

proxy_cache_path /tmp/cache keys_zone=cache:10m levels=1:2 inactive=600s max_size=100m;

server {
    listen 80 default_server;
    root /opt/app/public;
    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    access_log off;
    error_log  off;

    sendfile           off;

    client_max_body_size 100m;

    proxy_cache cache;
    proxy_cache_valid 200 1s;

    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass backend;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_intercept_errors off;
        fastcgi_buffer_size 16k;
        fastcgi_buffers 4 16k;
    }

    location ~ /\.ht {
        deny all;
    }
}

server {
    listen                     443 ssl http2;

    ssl                        on;
    ssl_protocols              TLSv1 TLSv1.1 TLSv1.2;
    ssl_certificate            nginx.crt;
    ssl_certificate_key        nginx.key;

    location / {
        proxy_pass          http://app_server;
        proxy_set_header    Host      $host;
        proxy_set_header    X-Real-IP $remote_addr;
        proxy_set_header    X-HTTPS   'True';
    }
}

EOT;

        return $nginxDefaultConfContents;
    }

    public static function getNginxConf(): string
    {
        $nginxConfContents = <<<'EOT'

user  nginx;
worker_processes  8;

error_log  /var/log/nginx/error.log warn;
pid        /var/run/nginx.pid;


events {
    worker_connections  2048;
}


http {
    include       /etc/nginx/mime.types;
    default_type  application/octet-stream;

    log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
                      '$status $body_bytes_sent "$http_referer" '
                      '"$http_user_agent" "$http_x_forwarded_for"';

    access_log  /var/log/nginx/access.log  main;

    sendfile        on;
    #tcp_nopush     on;

    keepalive_timeout  65;

    #gzip  on;

    include /etc/nginx/conf.d/*.conf;
}

EOT;

        return $nginxConfContents;
    }
}
