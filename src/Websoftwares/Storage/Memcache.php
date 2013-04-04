<?php
namespace Websoftwares\Storage;
/**
 * Memcache
 * Class for handling memcache cache
 *
 * @package Websoftwares
 * @subpackage Storage
 * @license http://philsturgeon.co.uk/code/dbad-license DbaD
 * @author Boris <boris@websoftwar.es>
 */
class Memcache implements \Websoftwares\CacheInterface
{
    /**
     * $expiration the expiration time
     * @var integer
     */
    protected $expiration = 3600;

    /**
     * $connection
     * @var Memcache
     */
    protected $connection = null;

    /**
     * setExpiration
     * Set the expiration time for the cache file.
     *
     * @param integer $expiration
     *
     * @return Memcache
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
     * Get the expiration time for the cache file.
     *
     * @return integer
     */
    protected function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * setConnection
     * Set the memcache connection
     *
     * @param $connection
     *
     * @return Memcache
     */
    public function setConnection(\Closure $connection = null)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * getConnection
     * Get the memcache connection
     *
     * @return Memcache
     */
    protected function getConnection()
    {
        $connection = $this->connection;

        if (!$connection() instanceof \Memcache) {
            throw new \Exception("No memcache object provided");
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

        return $this->getConnection()->set(md5($key),$value, MEMCACHE_COMPRESSED, $this->getExpiration());
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

        return $this->getConnection()->get(md5($key));
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

        return $this->getConnection()->delete(md5($key));
    }

    /**
     * add cache
     *
     * @param $key
     * @param $value
     *
     * @return boolean
     */
    public function add($key = null, $value = null)
    {
        if (!$key) {
            throw new \InvalidArgumentException($key . " is an invalid key argument");
        }
        if (!$value) {
            throw new \InvalidArgumentException($value . " is an invalid value argument");
        }

        return $this->getConnection()->add(md5($key),$value, MEMCACHE_COMPRESSED, $this->getExpiration());
    }

    /**
     * replace cache
     *
     * @param $key
     * @param $value
     *
     * @return boolean
     */
    public function replace($key = null, $value = null)
    {
        if (!$key) {
            throw new \InvalidArgumentException($key . " is an invalid key argument");
        }
        if (!$value) {
            throw new \InvalidArgumentException($value . " is an invalid value argument");
        }

        return $this->getConnection()->replace(md5($key),$value, MEMCACHE_COMPRESSED, $this->getExpiration());
    }
}
