<?php

namespace Tapatalk\Resources;

class UserRedis implements CacheStorageInterface
{
    // const STORAGE = 'redis';  // optional : ssdb
    
    // static public $storage = null;   // "user-data" redis, or ssdb

    // static public function storage()
    // {
    //     if (is_null(self::$storage)) {
    //         self::$storage = new \Redis();

    //         self::$storage->connect(config('api.userdata.connection.redis'));
    //     } 
        
    //     return self::$storage;
    // }

    /**
     * Rewrite "hmget" to support ssdb
     *
     * @param   string  $key
     * @param   array   $fields
     * @return  array
     */
    public function hmget($key, $fields = [], $value = '')
    {
        return self::storage()->hmget($key, $fields);
    }

    public function hget($key, $fields = [], $value = '')
    {
        return self::storage()->hget($key, $fields);
    }


    public function hgetall($key, $fields = [], $value = '')
    {
        return self::storage()->hgetall($key);
    }

    public function smembers($key, $fields = [], $value = '')
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
