<?php
namespace Websoftwares;

/**
 * CacheInterface
 * Interface defining methods that must implemented in the storage classes.
 *
 * @package Websoftwares
 * @author boris <boris@websoftwar.es>
 */
interface CacheInterface
{
    /**
     * save
     *
     * @param $key
     * @param $value
     */
    public function save($key,$value);

    /**
     * get
     *
     * @param $key
     */
    public function get($key);

    /**
     * delete
     *
     * @param $key
     */
    public function delete($key);
}
