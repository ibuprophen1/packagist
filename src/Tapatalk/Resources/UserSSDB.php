<?php

namespace Tapatalk\Resources;

class UserSSDB extends UserCacheBase
{
    static public $storage = null;   // "user-data" redis, or ssdb

    static public $connection;

    static public function storage()
    {
        if (is_null(self::$storage)) {
            self::$storage = new \Redis();

            self::$storage->connect(self::$connection);
        } 
        
        return self::$storage;
    }

    static public function setConnection($connection)
    {
        self::$connection = $connection;
    }

    /**
     * UserSSDB::$property_name(). E.g: UserSSDB::avatar()
     *
     * @param   string  $name
     * @param   array  $arguments
     * @return  string
     */
    // static public function __callStatic($name, $arguments)
    // {
    //     return self::storage()->hget($name);
    // }
    
    /**
     * SSDB don't support "Set", therefore using hash instead
     *
     * @param   string  $key
     * @param   array   $fields
     * @return  array
     */
    public function smembers($key, $fields = [], $value = '')
    {
        // return self::storage()->smembers($key);
        $arr = self::storage()->hgetall($key);
        return array_keys($arr);
    }

}
