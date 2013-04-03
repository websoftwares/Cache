<?php

use Websoftwares\Cache, Websoftwares\Storage\File;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->expiration = 3600;
        $this->path = 'cache';
    }

    public function testInstantiateAsObjectSucceeds()
    {
        $this->assertInstanceOf('Websoftwares\Storage\File', Cache::storage(new File()));
    }

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

        // Cleanup
        rmdir('cache');
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
            $this->assertEquals($this->$propertyName, $property->getValue(Cache::storage(new File())));
        }
    }

    /**
     * @expectedException Exception
     */
    public function testInstantiateAsObjectFails()
    {
        Cache::storage(new stdClass);
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
        $this->assertTrue(rmdir('cache'));
    }
}
