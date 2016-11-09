<?php

namespace Tapatalk\Resources;

class UserRedis extends UserCacheBase
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


    // static public function avatar()
    // {
    //     return self::storage()->hget("$key", $fields);
    // }

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    // public static function getInstance($connection)
    // {
    //     if (null === static::$instance) {
    //         static::$instance = new static();

    //         static::$storage = new \Redis();

    //         static::$storage->connect(self::$connection);

    //         // static::$instance->connections = $connections;  // only set connections when 1st time initiate
    //     }
        
    //     return static::$instance;
    // }    
}
