<?php

namespace Pipe;

use Closure;

class Config extends Singleton {

    //Table info
    public $table;
    public $columns = array();

    //PDO connection settings
    private $connection  = null;
    private $connections = array();
    public  $default_connection = '';
    public  $adapter = null;

    public function __construct()
    {
        //Work out the default connection based on environment
        //This for the default connection when set_connections() passes in an array of connections
        //and user hasnt manually selected the active one
        $this->default_connection = $this->get_environment();
    }

    public static function initialize(Closure $config)
    {
        // $config(self::instance('config'));
        $config(self::instance());
    }
    
    public function get_environment()
    {
        $env = '';
        
        if(strpos($_SERVER['SERVER_NAME'], 'local.') !== FALSE OR $_SERVER['SERVER_NAME'] == 'localhost' 
                  OR strpos($_SERVER['SERVER_NAME'], '.local') !== FALSE)
        {
            $env = 'development';
        }
        else
        {
            $env = 'production';
        }
        
        return $env;
    }
    
    public function connection($name = null)
    {
        if(is_null($name))
        {
            return $this->connection;
        }
        else
        {
            if(array_key_exists($name, $this->connections))
            {
                $this->connection = $this->connections[$name];
            }
            else
            {
                //Else we have passed in a DSN
                $this->connection = $name;
            }
        }
    }
    
    public function connections($connections = null)
    {
        if(is_null($connections))
        {
            return $this->connections;
        }
        else
        {
            if(is_array($connections))
            {
                $this->connections = $connections;
            }
            else
            {
                throw new PipeException("Make sure you pass an associated array of connections or use connection() for a single connection");
            }
        }
    }
}

?>