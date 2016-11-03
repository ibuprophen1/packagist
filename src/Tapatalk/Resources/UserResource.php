<?php 

namespace Tapatalk\Resources;

class UserResource
{
    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;
    
    protected $connections = [];   // "user-data" redis, or ssdb

    /**
     * By default, use ssdb connection
     *
     * @var  string
     */
    protected $primary_connection_type = 'ssdb';

    protected $au_id;

    /**
     * Hash key in redis/ssdb for user data: "APPUSER:7"
     *
     * @var  string
     */
    protected $key_user_data;

    // protected $ssdb;

    // protected $redis;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance($connections = [])
    {
        if (null === static::$instance) {
            static::$instance = new static();

            $this->setPrimaryConnectionType($connections);

            // if (isset($connections['ssdb'])) {
            //     $this->ssdb = new UserSSDB($connections['ssdb']);
            // }

            // if (isset($connections['redis'])) {
            //     $this->redis = new UserRedis($connections['redis']);
            // }

            // self::$storage = new \Redis();

            // foreach ($connections as $connection) {
            //     // $this->connections[] = 
            // }
            // self::$storage->connect(config('api.userdata.connection.redis'));
            // static::$instance->connections = $connections;  // only set connections when 1st time initiate
        }
        
        return static::$instance;
    }

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
        $this->au_id         = $au_id;
        $this->key_user_data = 'APPUSER:'.$au_id;

        return $this;
    }

    public function __get($properity_name)
    {
        if ( ! method_exists($this, $properity_name)) throw new Exception("Can't get properity \"$properity_name\".");

        if ($this->ssdb_connection && $this->redis_connection) {
            if ($this->primary_connection_type == 'ssdb') {
                try {
                    return UserSSDB::$properity_name();
                } catch (Exception $e) {
                    return UserRedis::$properity_name();
                }            
            } else {
                try {
                    return UserRedis::$properity_name();
                } catch (Exception $e) {
                    return UserSSDB::$properity_name();
                }
            }         
        } elseif ($this->ssdb_connection) {
            return UserSSDB::$properity_name();
        } elseif ($this->redis_connection) {
            return UserRedis::$properity_name();
        }

        #return $this->$name();
    }

    public function avatar()
    {
        // return self::storage()->hget("$key", $fields);

        return 'user avatars!!';
    }

    // static public function storage()
    // {
    //     if (is_null(self::$storage)) {
    //         self::$storage = new \Redis();

    //         self::$storage->connect(config('api.userdata.connection.redis'));
    //     } 
        
    //     return self::$storage;
    // }    
}
