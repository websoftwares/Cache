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
    public static function storage(CacheInterface $storage = null)
    {
        return $storage;
    }
}
