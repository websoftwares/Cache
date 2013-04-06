<?php
namespace Websoftwares\Storage;
/**
 * File
 * Class for handling file based cache
 *
 * @package Websoftwares
 * @subpackage Storage
 * @license http://philsturgeon.co.uk/code/dbad-license DbaD
 * @author Boris <boris@websoftwar.es>
 */
class File implements \Websoftwares\CacheInterface
{
    /**
     * $expiration the expiration time
     * @var integer
     */
    protected $expiration = 3600;

    /**
     * $path description
     * @var string
     */
    protected $path = 'cache';

    /**
     * setExpiration
     * Set the expiration time for the cache file.
     *
     * @param integer $expiration
     *
     * @return File
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
     * setPath
     * Set the the cache save path
     *
     * @param $path
     *
     * @return File
     */
    public function setPath($path = '')
    {
        if (! $path) {
            throw new \InvalidArgumentException($path . " is an invalid path argument");
        }
        $this->path = $path;

        return $this;
    }

    /**
     * getPath
     * Get the cache save path
     *
     * @return string
     */
    protected function getPath()
    {
        if (!file_exists($this->path)) {

            if (!@mkdir($this->path , 0777, true)) {
                throw new \OutOfRangeException("Error creating folder: " . $this->path);
            }
        }

        return $this->path;
    }

    /**
     * save cache to file
     *
     * @param $key
     * @param $value
     *
     * @return boolean
     * @author boris <boris@websoftwar.es>
     */
    public function save($key = null, $value = null)
    {
        if (!$key) {
            throw new \InvalidArgumentException($key . " is an invalid key argument");
        }
        if (!$value) {
            throw new \InvalidArgumentException($value . " is an invalid value argument");
        }

        return file_put_contents($this->fileName($key), gzcompress(serialize($value)),9) ? true : false;
    }

    /**
     * get cache from file
     *
     * @param $key
     *
     * @return mixed false/stored value
     */
    public function get($key)
    {
        $file = $this->fileName($key);

        if (!file_exists($file)) {
            throw new \OutOfRangeException("the file " . $file . " could not be retrieved");
        }

        if (filemtime($file) < (time() - $this->getExpiration())) {
            $this->delete($key);

            return false;
        }

        return unserialize(gzuncompress(file_get_contents($file)));
    }

    /**
     * delete cache file
     *
     * @param $key
     */
    public function delete($key)
    {
        $file = $this->fileName($key);

        if (!file_exists($file)) {
            throw new \OutOfRangeException("the file " . $file . " could not be deleted");
        }

        return unlink($file);
    }

    /**
     * fileName transform key into a md5 hashed string
     *
     * @param  string $key
     * @return string
     */
    private function fileName($key)
    {
        return $this->getPath() . DIRECTORY_SEPARATOR . md5($key) .'.cache';
    }
}
