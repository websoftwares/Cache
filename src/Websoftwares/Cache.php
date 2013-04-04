<?php
namespace Websoftwares;
/**
 * Cache
 * Yet another caching implementation
 *
 * @package Websoftwares
 * @license http://philsturgeon.co.uk/code/dbad-license DbaD
 * @author Boris <boris@websoftwar.es>
 */
class Cache
{
    /**
     * storage get storage and perform tasks
     * @param  Object $storage
     * @return Object
     */
    public static function storage(CacheInterface $storage = null)
    {
        return $storage;
    }
}
