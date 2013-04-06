<?php
namespace Websoftwares\Storage;
/**
 * Memcached
 * Class for handling memcached cache
 *
 * @package Websoftwares
 * @subpackage Storage
 * @license http://philsturgeon.co.uk/code/dbad-license DbaD
 * @author Boris <boris@websoftwar.es>
 */
class Memcached implements \Websoftwares\CacheInterface
{
    /**
     * $expiration the expiration time
     * @var integer
     */
    protected $expiration = 3600;

    /**
     * $connection
     * @var Memcached
     */
    protected $connection = null;

    /**
     * setExpiration
     * Set the expiration time for the cache file.
     *
     * @param integer $expiration
     *
     * @return Memcached
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
     * Set the memcached connection
     *
     * @param $connection
     *
     * @return Memcached
     */
    public function setConnection(\Closure $connection = null)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * getConnection
     * Get the memcached connection
     *
     * @return Memcached
     */
    protected function getConnection()
    {
        $connection = $this->connection;

        if (!$connection() instanceof \Memcached) {
            throw new \Exception("No memcached object provided");
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

        return $this->getConnection()->set($this->fileName($key),$value,$this->getExpiration());
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

        return $this->getConnection()->get($this->fileName($key));
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

        return $this->getConnection()->delete($this->fileName($key));
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

        return $this->getConnection()->add($this->fileName($key),$value,$this->getExpiration());
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

        return $this->getConnection()->replace($this->fileName($key),$value,$this->getExpiration());
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
