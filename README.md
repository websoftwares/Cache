# Cache

Yet another caching implementation.

[![Build Status](https://api.travis-ci.org/websoftwares/Cache.png)](https://travis-ci.org/websoftwares/Cache)

## Usage

Basic usage applies to all cache storage options

```php
use Websoftwares\Cache, Websoftwares\Storage\File;

$cache = Cache::storage(new File());

$cache->save('key',["a","b","c"]);

// Retrieve the cache

$cache->get('key');

```
## Storage

Available storage options:

*   Apc (saves the cache to apc)
*   File (saves the cache to a file)
*   Memcache (save the cache to a memcache instance)
*   Memcached (save the cache to a memcached instance)
*   MongoDB (save the cache to a mongo instance)
*   Redis (save the cache to a redis instance)
*   Riak (save the cache to a riak instance)

## Apc

```php
use Websoftwares\Cache, Websoftwares\Storage\Apc;

$apc = Cache::storage(new Apc())
 ->setExpiration(86400);

$apc->save('key',["a","b","c"]);

// Retrieve the cache

$apc->get('key');

```

## File

```php
use Websoftwares\Cache, Websoftwares\Storage\File;

$cache = Cache::storage(new File())
 ->setPath('/super/spot')
 ->setExpiration(86400);

$cache->save('key',["a","b","c"]);

// Retrieve the cache

$cache->get('key');

```

## Memcache

This requires u have the PHP memcache extension installed.

on Debian/Ubuntu systems for example install like this (requires administrative password).

```
sudo apt-get install php5-memcache

```

```php
use Websoftwares\Cache, Websoftwares\Storage\Memcache;

$memcache = Cache::storage(new Memcache())
    ->setConnection(function() {
        $instance = new \Memcache;
        $instance->connect('localhost','11211');
        return $instance;
    })
    ->setExpiration(86400);

$memcache->save('key',["a","b","c"]);

// Retrieve the cache

$memcache->get('key');

```

## Memcached

This requires u have the PHP memcached extension installed.

on Debian/Ubuntu systems for example install like this (requires administrative password).

```
sudo apt-get install php5-memcached

```

```php
use Websoftwares\Cache, Websoftwares\Storage\Memcached;

$memcached = Cache::storage(new Memcached())
    ->setConnection(function() {
        $instance = new \Memcached();
        $instance->addServer("localhost", 11211);
        return $instance;
    })
    ->setExpiration(86400);

$memcached->save('key',["a","b","c"]);

// Retrieve the cache

$memcached->get('key');

```

## Mongo
This storage option makes use of the "ensureIndex" method option "expireAfterSeconds".

This option can only be used if the following requirements are met.

Requirements:
*   Latest PHP Mongo extension installed
*   mongoDB deamon version 2.2+ | [read more](http://docs.mongodb.org/manual/tutorial/expire-data/ "More information")

On debian/ubuntu systems run the following command to install the mongo extension (requires administrator password).

```
sudo pecl install mongo
```

```php
use Websoftwares\Cache, Websoftwares\Storage\Mongo;

$mongo = Cache::storage(new Mongo())
    ->setConnection(function() {
        $m = new \MongoClient();
        $db = $m->mongocache;
        return $db;
    })
    ->setCollection('test')
    ->setExpiration(86400);

$mongo->save('key',["a","b","c"]);

// Retrieve the cache

$mongo->get('key');

```

## Redis

This requires u have the PHP [Predis](https://github.com/nrk/predis "Predis") package installed.

```php
use Websoftwares\Cache, Websoftwares\Storage\Redis;

$redis = Cache::storage(new Redis())
    ->setConnection(function() {
        $client = new \Predis\Client([
            'scheme'   => 'tcp',
            'host'     => '127.0.0.1',
            'port'     => 6379,
        ]);
        return $client;
    })
    ->setExpiration(86400);

$redis->save('key',["a","b","c"]);

// Retrieve the cache

$redis->get('key');

```

## Riak

This requires u have the PHP [Riak](https://github.com/basho/riak-php-client "Riak") official package installed.

```php
use Websoftwares\Cache, Websoftwares\Storage\Riak;

$riak = Cache::storage(new Riak())
    ->setConnection(function() {
        $client = new \Basho\Riak\Riak('127.0.0.1', 8098);
        return $client;
    })
    ->setBucket('testBucket')
    ->setExpiration(86400);

$riak->save('key',["a","b","c"]);

// Retrieve the cache

$riak->get('key');

```