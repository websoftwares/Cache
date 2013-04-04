<?php
namespace Websoftwares\Storage;
/**
 * Redis
 * Class for handling Redis cache
 *
 * @package Websoftwares
 * @subpackage Storage
 * @license http://philsturgeon.co.uk/code/dbad-license DbaD
 * @author Boris <boris@websoftwar.es>
 */
class Redis implements \Websoftwares\CacheInterface
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
     * $tag
     * @var string
     */
    protected $tag = 'cache';

    /**
     * getTag
     * @return string
     */
    protected function getTag()
    {
        return $this->tag;
    }

    /**
     * setTag
     * @param string $tag default cache
     *
     * @return Redis
     */
    public function setTag($tag = 'cache')
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * setExpiration
     * Set the expiration time for the cache.
     *
     * @param integer $expiration
     *
     * @return Redis
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
     * Set the redis connection
     *
     * @param $connection
     *
     * @return Redis
     */
    public function setConnection(\Closure $connection = null)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * getConnection
     * Get the redis connection
     *
     * @return Redis
     */
    protected function getConnection()
    {
        $connection = $this->connection;

        if (!$connection() instanceof \Predis\Client) {
            throw new \Exception("No redis client provided");
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
        $key = $this->fileName($key);
        $redis = $this->getConnection();

        if ($redis->set($key,serialize($value)) &&$redis->expire($key,$this->getExpiration())) {
            return true;
        } else {
            return false;
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

        return unserialize($this->getConnection()->get($this->fileName($key)));
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

        return $this->getConnection()->del($this->fileName($key)) ? true : false;
    }

    /**
     * exists cache
     *
     * @param $key
     */
    public function exists($key = null)
    {
        if (!$key) {
            throw new \InvalidArgumentException($key . " is an invalid key argument");
        }

        return $this->getConnection()->exists($this->fileName($key)) ? true : false;
    }

    /**
     * fileName transform key into a md5 hashed string
     *
     * @param  string $key
     * @return string
     */
    private function fileName($key)
    {
        return $this->getTag() . ':' . md5($key);
    }
}
