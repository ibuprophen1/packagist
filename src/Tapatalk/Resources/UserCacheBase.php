<?php

namespace Tapatalk\Resources;

/**
 * Put non-static functions here to be inherited
 */
class UserCacheBase
{
    /**
     * Rewrite "hmget" to support ssdb
     *
     * @param   string  $key
     * @param   array   $fields
     * @return  array
     */
    public function hmget($key, $fields = [])
    {
        return self::storage()->hmget($key, $fields);
    }

    public function hget($key, $field)
    {
        return self::storage()->hget($key, $field);
    }

    public function hgetall($key)
    {
        return self::storage()->hgetall($key);
    }

    public function smembers($key)
    {
        return self::storage()->smembers($key);
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
        return self::storage()->hset($key, $field, $value);
    }    
}
