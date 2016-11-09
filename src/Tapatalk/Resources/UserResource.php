<?php 

namespace Tapatalk\Resources;

class UserResource
{
    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;
    
    /**
     * "user-data" redis, or ssdb
     *
     * @var  array
     */
    protected $connections = [];

    /**
     * By default, use ssdb connection
     *
     * @var  string
     */
    protected $primary_connection_type = 'ssdb';

    /**
     * Current request's au_id
     *
     * @var  int
     */
    protected $au_id;

    /**
     * Hash key in redis/ssdb for user data: "APPUSER:7"
     *
     * @var  string
     */
    protected $redis_key;

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct()
    {
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance($connections = [])
    {
        if (null === static::$instance) {
            static::$instance = new static();

            static::$instance->setPrimaryConnectionType($connections);

            if (isset($connections['ssdb'])) {
                static::$instance->ssdb_connection = $connections['ssdb'];

                UserSSDB::setConnection($connections['ssdb']);
            }

            if (isset($connections['redis'])) {
                static::$instance->redis_connection = $connections['redis'];

                UserRedis::setConnection($connections['redis']);
            }
        }
        
        return static::$instance;
    }

    /**
     * Set primary connectiont type (default "ssdb")
     *
     * @param  array  $connections
     * @return  void
     */
    private function setPrimaryConnectionType($connections = [])
    {
        $connection_types = array_keys($connections);

        if ($connection_types[0] == 'redis') {
            $this->primary_connection_type = 'redis';
        }
    }

    /**
     * Setter for au_id
     *
     * @param   int  $au_id
     * @return  $this
     */
    public function user($au_id)
    {
        $this->au_id     = $au_id;
        $this->redis_key = 'APPUSER:'.$au_id;

        return $this;
    }

    /**
     * Handle single proerty request. E.g: $resource->user(100)->avatar, $property_name : avatar
     *
     * @param   string  $property_name
     * @return  string
     */
    public function __get($property_name)
    {
        $result = null;

        if ($this->ssdb_connection && $this->redis_connection) {
            if ($this->primary_connection_type == 'ssdb') {
                try {
                    $result = UserSSDB::storage()->hget($this->redis_key, $property_name);
                } catch (\RedisException $e) {
                    $result = UserRedis::storage()->hget($this->redis_key, $property_name);
                }            
            } else {
                try {
                    $result = UserRedis::storage()->hget($this->redis_key, $property_name);
                } catch (\RedisException $e) {
                    $result = UserSSDB::storage()->hget($this->redis_key, $property_name);
                }
            }         
        } elseif ($this->ssdb_connection) {
            $result = UserSSDB::storage()->hget($this->redis_key, $property_name);
            // return UserSSDB::$property_name();
        } elseif ($this->redis_connection) {
            $result = UserRedis::storage()->hget($this->redis_key, $property_name);
            // return UserRedis::$property_name();
        }

        return (string) $result;
        #return $this->$name();
    }

    /**
     * Get multiple user properties. E.g: $resource->user(100)->get(['email', 'avatar']);
     *
     * @param   array  $property_names
     * @return  array
     */
    public function get($property_names)
    {
        // Support getting single property using : $resource->user(100)->get('email') :
        if (is_string($property_names)) {
            return $this->__get($property_names);
        }

        if ( ! is_array($property_names)) throw new Exception("Invalid params. Accept string or array of user properties.");
        
        if ($this->ssdb_connection && $this->redis_connection) {
            if ($this->primary_connection_type == 'ssdb') {
                try {
                    return UserSSDB::storage()->hmget($this->redis_key, $property_names);
                } catch (\RedisException $e) {
                    return UserRedis::storage()->hmget($this->redis_key, $property_names);
                }            
            } else {
                try {
                    return UserRedis::storage()->hmget($this->redis_key, $property_names);
                } catch (\RedisException $e) {
                    return UserSSDB::storage()->hmget($this->redis_key, $property_names);
                }
            }         
        } elseif ($this->ssdb_connection) {
            return UserSSDB::storage()->hmget($this->redis_key, $property_names);
        } elseif ($this->redis_connection) {
            return UserRedis::storage()->hmget($this->redis_key, $property_names);
        }
    } 
}
