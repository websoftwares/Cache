<?php
namespace Websoftwares\Storage;
/**
 * Riak
 * Class for handling Riak cache
 *
 * @package Websoftwares
 * @subpackage Storage
 * @license http://philsturgeon.co.uk/code/dbad-license DbaD
 * @author Boris <boris@websoftwar.es>
 */
class Riak implements \Websoftwares\CacheInterface
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
     * $bucket
     * @var string
     */
    protected $bucket = 'cache';

    /**
     * getBucket
     *
     * @return string
     */
    public function getBucket()
    {
        return $this->bucket;
    }

    /**
     * setBucket
     * @param string $bucket
     *
     * @return Riak
     */
    public function setBucket($bucket = 'cache')
    {
        $this->bucket = $bucket;

        return $this;
    }

    /**
     * setExpiration
     * Set the expiration time for the cache.
     *
     * @param integer $expiration
     *
     * @return Riak
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
     * Set the riak connection
     *
     * @param $connection
     *
     * @return Riak
     */
    public function setConnection(\Closure $connection = null)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * getConnection
     * Get the riak connection
     *
     * @return Riak
     */
    protected function getConnection()
    {
        $connection = $this->connection;

        if (!$connection() instanceof \Basho\Riak\Riak) {
            throw new \Exception("No riak client provided");
        }

        return $connection();
    }

    /**
     * save cache
     *
     * @param $key
     * @param $value
     *
     * @return boolean
     */
    public function save($key = null, $value = null)
    {
        if (!$key) {
            throw new \InvalidArgumentException($key . " is an invalid key argument");
        }
        if (!$value) {
            throw new \InvalidArgumentException($value . " is an invalid value argument");
        }

        $riak = $this->getConnection();
        $bucket = $riak->bucket($this->getBucket());

        $result = $bucket
            ->newObject($this->fileName($key),[
                'data' => $value,
                'expiration' => time() + $this->getExpiration()
                ])
            ->store();

        return $result ? true : false;
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

        $riak = $this->getConnection();
        $bucket = $riak->bucket($this->getBucket());
        $dataObj = $bucket->get($this->fileName($key));

        if ($dataObj->exists()) {

            $result = $dataObj->getData();
            if (time() > $result['expiration']) {
                return false;
            }

            return $result['data'];

        } else {
            return false;
        }
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
        $riak = $this->getConnection();
        $bucket = $riak->bucket($this->getBucket());
        $dataObj = $bucket->get($this->fileName($key));

        return $dataObj->delete() ? true : false;
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
