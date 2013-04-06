<?php
namespace Websoftwares\Storage;
/**
 * Apc
 * Class for handling apc based cache
 *
 * @package Websoftwares
 * @subpackage Storage
 * @license http://philsturgeon.co.uk/code/dbad-license DbaD
 * @author Boris <boris@websoftwar.es>
 */
class Apc implements \Websoftwares\CacheInterface
{
    /**
     * $expiration the expiration time
     * @var integer
     */
    protected $expiration = 3600;

    /**
     * setExpiration
     * Set the expiration time for the cache apc.
     *
     * @param integer $expiration
     *
     * @return Apc
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
     * Get the expiration time for the cache apc.
     *
     * @return integer
     */
    protected function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * save cache to apc
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

        return apc_add($this->fileName($key),$value,$this->getExpiration());
    }

    /**
     * store cache to apc
     *
     * @param $key
     * @param $value
     *
     * @return boolean
     */
    public function store($key = null, $value = null)
    {
        if (!$key) {
            throw new \InvalidArgumentException($key . " is an invalid key argument");
        }
        if (!$value) {
            throw new \InvalidArgumentException($value . " is an invalid value argument");
        }

        return apc_store($this->fileName($key),$value,$this->getExpiration());
    }

    /**
     * get cache from apc
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

        return apc_fetch($this->fileName($key));
    }

    /**
     * delete cache apc
     *
     * @param $key
     * @author boris <boris@websoftwar.es>
     */
    public function delete($key = null)
    {
        if (!$key) {
            throw new \InvalidArgumentException($key . " is an invalid key argument");
        }

        return apc_delete($this->fileName($key));
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

        return apc_exists($this->fileName($key));
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
