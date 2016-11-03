<?php

namespace Tapatalk\Resources;

class UserSSDB implements CacheStorageInterface
{
    static public $storage = null;   // "user-data" redis, or ssdb

    static public function storage()
    {
        if (is_null(self::$storage)) {
            self::$storage = new \Redis();

            self::$storage->connect(config('api.userdata.connection.ssdb'));
        } 
        
        return self::$storage;
    }

    /**
     * Rewrite "hmget" to support ssdb
     *
     * @param   string  $key
     * @param   array   $fields
     * @return  array
     */
    public static function hmget($key, $fields = [], $value = '')
    {
        return self::storage()->hmget($key, $fields);
    }

    public static function hget($key, $fields = [], $value = '')
    {
        return self::storage()->hget($key, $fields);
    }
    
    public static function hgetall($key, $fields = [], $value = '')
    {
        return self::storage()->hgetall($key);
    }

    /**
     * SSDB don't support "Set", therefore using hash instead
     *
     * @param   string  $key
     * @param   array   $fields
     * @return  array
     */
    public static function smembers($key, $fields = [], $value = '')
    {
        // return self::storage()->smembers($key);
        $arr = self::storage()->hgetall($key);
        return array_keys($arr);
    }

    /**
     * Hset($key, $field, $value)
     *
     * @param   string  $key
     * @param   string  $field
     * @param   string  $value
     * @return  void
     */
    public static function hset($key, $field = '', $value = '')
    {
        return self::storage()->hset($key, $field, $value);
    }      
}
