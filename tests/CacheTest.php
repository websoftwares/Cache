<?php

use Websoftwares\Cache,
    Websoftwares\Storage\File,
    Websoftwares\Storage\Memcache,
    Websoftwares\Storage\Redis,
    Websoftwares\Storage\Riak;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */
    public function setUp()
    {
        $this->expiration = 3600;
        $this->path = 'cache';
        $this->connection = null;
        $this->tag = 'cache';
        $this->bucket = 'cache';
    }

    public function testInstantiateAsObjectSucceeds()
    {
        $this->assertInstanceOf('Websoftwares\Storage\File', Cache::storage(new File()));
        $this->assertInstanceOf('Websoftwares\Storage\Memcache', Cache::storage(new Memcache()));
        $this->assertInstanceOf('Websoftwares\Storage\Redis', Cache::storage(new Redis()));
        $this->assertInstanceOf('Websoftwares\Storage\Riak', Cache::storage(new Riak()));
    }

    /**
     * @expectedException Exception
     */
    public function testInstantiateAsObjectFails()
    {
        Cache::storage(new stdClass);
    }

    /*
    |--------------------------------------------------------------------------
    | Tests for file cache
    |--------------------------------------------------------------------------
    */
    public function testCacheStorageFileSaveSucceeds()
    {
        $this->assertTrue(Cache::storage(new File())->save('test',range('c', 'a')));

        Cache::storage(new File())->delete('test');
        // Cleanup
        rmdir('cache');
    }

    public function testCacheStorageFileDeleteSucceeds()
    {
        Cache::storage(new File())->save('test',range('c', 'a'));

        $this->assertTrue(Cache::storage(new File())->delete('test'));
        // Cleanup
        rmdir('cache');
    }

    public function testCacheStorageFileGetSucceeds()
    {
        $cache = Cache::storage(new File());
        $cache->save('test',range('c', 'a'));
        $expected = ['c','b','a'];
        $this->assertEquals($cache->get('test'), $expected);

        Cache::storage(new File())->delete('test');
        // Cleanup
        rmdir('cache');
    }
    public function testCacheStorageFileExpiration()
    {
        $cache = Cache::storage(new File());
        $cache
            ->setExpiration(2)
            ->save('test',range('c', 'a'));

        sleep(1);
        $expected = ['c','b','a'];
        $this->assertEquals($cache->get('test'), $expected);

        sleep(3);
        $this->assertFalse($cache->get('test'));
    }

    public function testCacheStorageFilePath()
    {
        $path = 'c4ch3d';

        Cache::storage(new File())
            ->setPath($path)
            ->save('test',range('c', 'a'));

        Cache::storage(new File())
            ->setPath($path)
            ->delete('test');

        $this->assertTrue(is_dir($path));

        // Cleanup
        rmdir($path);
    }

    public function testCacheStorageFilePropertyValuesSucceeds()
    {
        $cache = new ReflectionClass(Cache::storage(new File()));

        foreach ($cache->getProperties() as $property) {

            $property->setAccessible(true);
            $propertyName = $property->name;

            if (property_exists($this, $propertyName)) {
                $this->assertEquals($this->$propertyName, $property->getValue(Cache::storage(new File())));
            }
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageFileSetExpirationFails()
    {
        Cache::storage(new File())->setExpiration('test');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageFileSetPathFails()
    {
        Cache::storage(new File())->setPath();
    }

    /**
     * @expectedException OutOfRangeException
     */
    public function testCacheStorageFileGetPathFails()
    {
        // if this works u have rw permission to your / folder
        $cache = Cache::storage(new File())->setPath('/root2');

        $cacheReflection = new ReflectionClass($cache);
        $getPath = $cacheReflection->getMethod('getPath');
        $getPath->setAccessible(true);

        $getPath->invoke($cache);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageFileSaveFails()
    {
        Cache::storage(new File())->save();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageFileSaveValueFails()
    {
        Cache::storage(new File())->save('test');
    }

    /**
     * @expectedException OutOfRangeException
     */
    public function testCacheStorageFileGetFails()
    {
        Cache::storage(new File())->get('test');
    }

    /**
     * @expectedException OutOfRangeException
     */
    public function testCacheStorageFileDeleteFails()
    {
        Cache::storage(new File())->delete('test');
    }

    public function testCleanupDirectory()
    {
        rmdir('cache');
    }

    /*
    |--------------------------------------------------------------------------
    | Tests for memcache cache
    |--------------------------------------------------------------------------
    */
    public function testCacheStorageMemcacheSaveSucceeds()
    {
        $memcache = Cache::storage(new Memcache())
            ->setConnection(function() {
                $instance = new \Memcache;
                $instance->connect('localhost','11211');

                return $instance;
            })
            ->setExpiration(1);

        $this->assertTrue($memcache->save('test',range('c', 'a')));
    }

    public function testCacheStorageMemcacheAddSucceeds()
    {
        $memcache = Cache::storage(new Memcache())
            ->setConnection(function() {
                $instance = new \Memcache;
                $instance->connect('localhost','11211');

                return $instance;
            })
            ->setExpiration(1);

        $this->assertTrue($memcache->add('test2',range('c', 'a')));
    }

    public function testCacheStorageMemcacheReplaceSucceeds()
    {
        $memcache = Cache::storage(new Memcache())
            ->setConnection(function() {
                $instance = new \Memcache;
                $instance->connect('localhost','11211');

                return $instance;
            })
            ->setExpiration(1);

        $this->assertTrue($memcache->replace('test',range('c', 'a')));
    }

    public function testCacheStorageMemcacheDeleteSucceeds()
    {
        $memcache = Cache::storage(new Memcache())
            ->setConnection(function() {
                $instance = new \Memcache;
                $instance->connect('localhost','11211');

                return $instance;
            });

        $this->assertTrue($memcache->delete('test'));
        $this->assertTrue($memcache->delete('test2'));
    }

    public function testCacheStorageMemcacheGetSucceeds()
    {
        $memcache = Cache::storage(new Memcache())
            ->setConnection(function() {
                $instance = new \Memcache;
                $instance->connect('localhost','11211');

                return $instance;
            })
            ->setExpiration(5);

        $memcache->save('test',range('c', 'a'));
        $expected = ['c','b','a'];
        $this->assertEquals($memcache->get('test'), $expected);

        $memcache->delete('test');
    }

    public function testCacheStorageMemcacheExpiration()
    {
        $memcache = Cache::storage(new Memcache())
            ->setConnection(function() {
                $instance = new \Memcache;
                $instance->connect('localhost','11211');

                return $instance;
            })
            ->setExpiration(5);

        $memcache
            ->save('test',range('c', 'a'));

        $expected = ['c','b','a'];
        $this->assertEquals($memcache->get('test'), $expected);

        sleep(5);
        $this->assertFalse($memcache->get('test'));
        $memcache->delete('test');
    }

    public function testCacheStorageMemcachePropertyValuesSucceeds()
    {
        $cache = new ReflectionClass(Cache::storage(new Memcache()));

        foreach ($cache->getProperties() as $property) {

            $property->setAccessible(true);
            $propertyName = $property->name;

            if (property_exists($this, $propertyName)) {
                $this->assertEquals($this->$propertyName, $property->getValue(Cache::storage(new Memcache())));
            }
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageMemcacheSetExpirationFails()
    {
        Cache::storage(new Memcache())->setExpiration('test');
    }

    /**
     * @expectedException Exception
     */
    public function testCacheStorageMemcacheGetConnectionInstanceOfMemcacheFails()
    {
        $memcache = Cache::storage(new Memcache())
            ->setConnection(function() {
                return new \stdClass;
            });

        $memcacheReflection = new ReflectionClass($memcache);
        $getConnection = $memcacheReflection->getMethod('getConnection');
        $getConnection->setAccessible(true);

        $getConnection->invoke($memcache);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageMemcacheSaveFails()
    {
        Cache::storage(new Memcache())->save();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageMemcacheSaveValueFails()
    {
        Cache::storage(new Memcache())->save('test');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageMemcacheAddFails()
    {
        Cache::storage(new Memcache())->add();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageMemcacheAddValueFails()
    {
        Cache::storage(new Memcache())->add('test');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageMemcacheReplaceFails()
    {
        Cache::storage(new Memcache())->replace();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageMemcacheReplaceValueFails()
    {
        Cache::storage(new Memcache())->replace('test');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageMemcacheGetFails()
    {
        Cache::storage(new Memcache())->get();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageMemcacheDeleteFails()
    {
        Cache::storage(new Memcache())->delete();
    }
    /*
    |--------------------------------------------------------------------------
    | Tests for redis cache
    |--------------------------------------------------------------------------
    */
    public function testCacheStorageRedisSaveSucceeds()
    {
        $redis = Cache::storage(new Redis())
            ->setConnection(function() {
                $client = new \Predis\Client([
                    'scheme'   => 'tcp',
                    'host'     => '127.0.0.1',
                    'port'     => 6379,
                ]);

                return $client;
            })
            ->setExpiration(50);

        $this->assertTrue($redis->save('test',range('c', 'a')));
    }

    public function testCacheStorageRedisExistsSucceeds()
    {
        $redis = Cache::storage(new Redis())
            ->setConnection(function() {
                $client = new \Predis\Client([
                    'scheme'   => 'tcp',
                    'host'     => '127.0.0.1',
                    'port'     => 6379,
                ]);

                return $client;
            });

        $this->assertTrue($redis->exists('test'));
    }

    public function testCacheStorageRedisDeleteSucceeds()
    {
        $redis = Cache::storage(new Redis())
            ->setConnection(function() {
                $client = new \Predis\Client([
                    'scheme'   => 'tcp',
                    'host'     => '127.0.0.1',
                    'port'     => 6379,
                ]);

                return $client;
            });

        $this->assertTrue($redis->delete('test'));
    }

    public function testCacheStorageRedisGetSucceeds()
    {
        $redis = Cache::storage(new Redis())
            ->setConnection(function() {
                $client = new \Predis\Client([
                    'scheme'   => 'tcp',
                    'host'     => '127.0.0.1',
                    'port'     => 6379,
                ]);

                return $client;
            })
            ->setExpiration(5);

        $redis->save('test',range('c', 'a'));
        $expected = ['c','b','a'];
        $this->assertEquals($redis->get('test'), $expected);

        $redis->delete('test');
    }

    public function testCacheStorageRedisExpiration()
    {
        $redis = Cache::storage(new Redis())
            ->setConnection(function() {
                $client = new \Predis\Client([
                    'scheme'   => 'tcp',
                    'host'     => '127.0.0.1',
                    'port'     => 6379,
                ]);

                return $client;
            })
            ->setExpiration(1);

        $redis
            ->save('test',range('c', 'a'));

        $expected = ['c','b','a'];
        $this->assertEquals($redis->get('test'), $expected);

        sleep(3);
        $this->assertFalse($redis->get('test'));
        $redis->delete('test');
    }

    public function testCacheStorageRedisPropertyValuesSucceeds()
    {
        $cache = new ReflectionClass(Cache::storage(new Redis()));

        foreach ($cache->getProperties() as $property) {

            $property->setAccessible(true);
            $propertyName = $property->name;

            if (property_exists($this, $propertyName)) {
                $this->assertEquals($this->$propertyName, $property->getValue(Cache::storage(new Redis())));
            }
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageRedisSetExpirationFails()
    {
        Cache::storage(new Redis())->setExpiration('test');
    }

    /**
     * @expectedException Exception
     */
    public function testCacheStorageRedisConnectionInstanceOfPredisClientFails()
    {
        $redis = Cache::storage(new Redis())
            ->setConnection(function() {
                return new \stdClass;
            });

        $redisReflection = new ReflectionClass($redis);
        $getConnection = $redisReflection->getMethod('getConnection');
        $getConnection->setAccessible(true);

        $getConnection->invoke($redis);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageRedisSaveFails()
    {
        Cache::storage(new Redis())->save();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageRedisSaveValueFails()
    {
        Cache::storage(new Redis())->save('test');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageRedisGetFails()
    {
        Cache::storage(new Redis())->get();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageRedisDeleteFails()
    {
        Cache::storage(new Redis())->delete();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageRedisExistsFails()
    {
        Cache::storage(new Redis())->exists();
    }
    /*
    |--------------------------------------------------------------------------
    | Tests for riak cache
    |--------------------------------------------------------------------------
    */
    public function testCacheStorageRiakSaveSucceeds()
    {
        $riak = Cache::storage(new Riak())
            ->setConnection(function() {
                $client = new \Basho\Riak\Riak('127.0.0.1', 8098);

                return $client;
            })
            ->setBucket('testBucket')
            ->setExpiration(50);

        $this->assertTrue($riak->save('test',range('c', 'a')));
    }

    public function testCacheStorageRiakDeleteSucceeds()
    {
        $riak = Cache::storage(new Riak())
            ->setConnection(function() {
                $client = new \Basho\Riak\Riak('127.0.0.1', 8098);

                return $client;
            })
            ->setBucket('testBucket');

        $this->assertTrue($riak->delete('test'));
    }

    public function testCacheStorageRiakGetSucceeds()
    {
        $riak = Cache::storage(new Riak())
            ->setConnection(function() {
                $client = new \Basho\Riak\Riak('127.0.0.1', 8098);

                return $client;
            })
            ->setBucket('testBucket')
            ->setExpiration(5);

        $riak->save('test',range('c', 'a'));
        $expected = ['c','b','a'];
        $this->assertEquals($riak->get('test'), $expected);

        $riak->delete('test');
    }

    public function testCacheStorageRiakExpiration()
    {
        $riak = Cache::storage(new Riak())
            ->setConnection(function() {
                $client = new \Basho\Riak\Riak('127.0.0.1', 8098);

                return $client;
            })
            ->setBucket('testBucket')
            ->setExpiration(1);

        $riak
            ->save('test',range('c', 'a'));

        $expected = ['c','b','a'];
        $this->assertEquals($riak->get('test'), $expected);

        sleep(3);
        $this->assertFalse($riak->get('test'));
        $riak->delete('test');
    }

    public function testCacheStorageRiakPropertyValuesSucceeds()
    {
        $cache = new ReflectionClass(Cache::storage(new Riak()));

        foreach ($cache->getProperties() as $property) {

            $property->setAccessible(true);
            $propertyName = $property->name;

            if (property_exists($this, $propertyName)) {
                $this->assertEquals($this->$propertyName, $property->getValue(Cache::storage(new Riak())));
            }
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageRiakSetExpirationFails()
    {
        Cache::storage(new Riak())->setExpiration('test');
    }

    /**
     * @expectedException Exception
     */
    public function testCacheStorageRiakConnectionInstanceOfPredisClientFails()
    {
        $redis = Cache::storage(new Riak())
            ->setConnection(function() {
                return new \stdClass;
            });

        $riakReflection = new ReflectionClass($riak);
        $getConnection = $riakReflection->getMethod('getConnection');
        $getConnection->setAccessible(true);

        $getConnection->invoke($riak);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageRiakSaveFails()
    {
        Cache::storage(new Riak())->save();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageRiakSaveValueFails()
    {
        Cache::storage(new Riak())->save('test');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageRiakGetFails()
    {
        Cache::storage(new Riak())->get();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCacheStorageRiakDeleteFails()
    {
        Cache::storage(new Riak())->delete();
    }
}
