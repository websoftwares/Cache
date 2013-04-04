# Cache

Yet another caching implementation.

[![Build Status](https://api.travis-ci.org/websoftwares/Cache.png)](https://travis-ci.org/websoftwares/Cache)

## Usage

Basic usage applies to all cache storage options

```php
<?php
use Websoftwares\Cache, Websoftwares\Storage\File;

Cache::storage(new File())->save('key',["a","b","c"]);

// Retrieve the cache

Cache::storage(new File())->get('key');

```
## Storage

Available storage options:

*   File (saves the cache to a file)
*   Memcache (save the cache to an memcache instance)

## File

```php
<?php
use Websoftwares\Cache, Websoftwares\Storage\File;

$cache = Cache::storage(new File())
 ->setPath('/super/spot')
 ->setExpiration(86000);

$cache->save('key',["a","b","c"]);

// Retrieve the cache

$cache->get('key');

```

## Memcache

This requires u have the PHP memcache extension installed.

on Debian/Ubuntu systems for example install ike this (requires administrative password).

```
sudo apt-get install php5-memcache

```

```php
<?php
use Websoftwares\Cache, Websoftwares\Storage\Memcache;

$cache = Cache::storage(new File())
 ->setPath('/super/spot')
 ->setExpiration(86000);

$cache->save('key',["a","b","c"]);

// Retrieve the cache

$cache->get('key');

```