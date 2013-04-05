<?php
namespace Websoftwares\Storage;
/**
 * Mongo
 * Class for handling Mongo cache
 *
 * @package Websoftwares
 * @subpackage Storage
 * @license http://philsturgeon.co.uk/code/dbad-license DbaD
 * @author Boris <boris@websoftwar.es>
 */
class Mongo implements \Websoftwares\CacheInterface
{
    /**
     * $expiration the expiration time
     * @var integer
     */
    protected $expiration = 3600;

    /**
     * $connection
     * @var object
     */
    protected $connection = null;

    /**
     * $collection
     * @var string
     */
    protected $collection = 'cache';

    /**
     * getCollection
     *
     * @return string
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * setCollection
     * @param string $collection
     *
     * @return Mongo
     */
    public function setCollection($collection = 'cache')
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * setExpiration
     * Set the expiration time for the cache.
     *
     * @param integer $expiration
     *
     * @return Mongo
     */
    public function setExpiration($expiration = 0)
    {
        $expiration = (int) $expiration;

        if ($expiration > 0) {
            $this->expiration = $expiration;
        } else {
            throw new \InvalidArgumentException($expiration . " is an invalid expiration argument");
        }

        return $this;
    }

    /**
     * getExpiration
     * Get the expiration time for the cache.
     *
     * @return integer
     */
    protected function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * setConnection
     * Set the mongo connection
     *
     * @param $connection
     *
     * @return Mongo
     */
    public function setConnection(\Closure $connection = null)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * getConnection
     * Get the mongo connection
     *
     * @return Mongo
     */
    protected function getConnection()
    {
        $connection = $this->connection;

        if (!$connection() instanceof \MongoDB) {
            throw new \Exception("No mongo client provided");
        }

        return $connection();
    }

    /**
     * save cache
     *
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function save($key = null, $value = null)
    {
        if (!$key) {
            throw new \InvalidArgumentException($key . " is an invalid key argument");
        }
        if (!$value) {
            throw new \InvalidArgumentException($value . " is an invalid value argument");
        }

        $collectionName =  $this->getCollection();
        $collection = $this->getConnection()->$collectionName;

        try {

            $collection->deleteIndex("expiration");

            $collection->ensureIndex(
                ["key" => 1],
                ["unique" => true,
                 "dropDups" => 1
                ]
            );

            $collection->ensureIndex(
                ["expiration" => 1],
                ["expireAfterSeconds" => $this->getExpiration()]
            );

            $collection->insert([
                "key" => $this->fileName($key),
                "expiration" => new \MongoDate(),
                "data" => $value
                ]
            );

            return true;

        } catch(\MongoCursorException $e) {
            throw $e;
        }
    }

    /**
     * get cache
     *
     * @param $key
     *
     * @return mixed false/stored value
     */
    public function get($key = null)
    {
        if (!$key) {
            throw new \InvalidArgumentException($key . " is an invalid key argument");
        }

        $collectionName =  $this->getCollection();
        $collection = $this->getConnection()->$collectionName;

        $result = $collection->findOne(array('key' => $this->fileName($key)));

        return $result ? $result['data'] : false;
    }

    /**
     * delete cache
     *
     * @param $key
     */
    public function delete($key = null)
    {
        if (!$key) {
            throw new \InvalidArgumentException($key . " is an invalid key argument");
        }

        $collectionName =  $this->getCollection();
        $collection = $this->getConnection()->$collectionName;

        return $collection
            ->remove(
                ["key" => $this->fileName($key)],
                ["justOne" => true])
            ? true
            : false;
    }

    /**
     * fileName transform key into a md5 hashed string
     *
     * @param  string $key
     * @return string
     */
    private function fileName($key)
    {
        return md5($key);
    }
}
