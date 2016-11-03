<?php

namespace Tapatalk\Resources;

interface CacheStorageInterface
{
    /**
     * Rewrite "hmget" to support ssdb
     *
     * @param   string  $key
     * @param   array   $fields
     * @return  array
     */
    public function hmget($key, $fields = [], $value = '')
    {
    }

    public function hget($key, $fields = [], $value = '')
    {
    }

    public function hgetall($key, $fields = [], $value = '')
    {
    }

    public function smembers($key, $fields = [], $value = '')
    {
    }

    /**
     * Hset($key, $field, $value)
     *
     * @param   string  $key
     * @param   string  $field
     * @param   string  $value
     * @return  void
     */
    public function hset($key, $field = '', $value = '')
    {
    }    
}
