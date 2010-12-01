<?php

namespace Pipe;

use PDO;
use PDOException;

class Connection extends Singleton {

    public $connection = null;
    
    static $PDO_OPTIONS = array(
        PDO::ATTR_CASE				=> PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE			=> PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS		=> PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES	=> false
    );

    public static function initialize()
    {
        $config = self::instance('config');
        
        if(is_null($config->connection()))
        {
            //User has set a list of connections but hasnt used connection('dev')
            //Using environment default to set
            $default     = $config->default_connection;
            $connections = $config->connections();
            
            if(array_key_exists($default, $connections))
            {
                //Set the connection string to use
                $config->connection($default);
            }
            else
            {
                //We have a problem
                //User hasnt set a DSN for environment
                $keys = implode(',', array_keys($connections));
                $keys = (strlen($keys) > 0) ? $keys : 'None!';
                
                throw new PipeException("`$default` does not exist in the connections list. Available connections: $keys");
            }
        }
        
        //Parse connection string
        $info    = self::parse_connection_string($config->connection());
        
        //Load adapter for DB
        $adapter = self::load_adapter($info->protocol);
        
        //Create PDO object using adapter
        try
        {
            $pdo = new $adapter($info);
            
            //Check adapter is extending abstract Adapter class
            list($package, $class_name) = explode('\\', get_parent_class($pdo));
            
            if($class_name !== "Adapter")
            {
                throw new PipeException("Adapter $adapter must implement abstract class `Adapter` in the same folder");
            }
        }
        catch(PDOException $e)
        {
            throw new PipeException($e);
        }
        
        //Save connection now its been created to Pipe\Config
        $config->adapter = $pdo;
    }
    
    private static function parse_connection_string($con_str)
    {
        //Check string is correct
        if(strpos($con_str, '://') === false)
        {
            throw new PipeException("$con_str: is not a valid connection string");
        }
        
        //Lets parse and get the details needed
        $url = @parse_url($con_str);
        
        if(!isset($url['host']))
        {
            throw new PipeException('Database host must be specified in the connection string');
        }
        
        $info = new \stdClass();
        $info->protocol = $url['scheme'];
		$info->host		= $url['host'];
		$info->db		= isset($url['path']) ? substr($url['path'],1) : null;
		$info->user		= isset($url['user']) ? $url['user'] : null;
		$info->pass		= isset($url['pass']) ? $url['pass'] : null;
        
        if ($info->host == 'unix(')
        {
            $socket_database = $info->host . '/' . $info->db;

            if (preg_match_all('/^unix\((.+)\)\/(.+)$/', $socket_database, $matches) > 0)
            {
                $info->host = $matches[1][0];
                $info->db = $matches[2][0];
            }
        }
        
        if(isset($url['port']))
        {
            $info->port = $url['port'];
        }
        
        if (strpos($connection_url,'decode=true') !== false)
        {
            if ($info->user)
            {
                $info->user = urldecode($info->user);
            }

            if ($info->pass)
            {
                $info->pass = urldecode($info->pass);
            }
        }

        return $info;
    }
    
    private static function load_adapter($protocol)
    {
        $class     = ucwords($protocol).'Adapter';
        $pipeclass = 'Pipe\\'.$class;
        
        $adclass = dirname(__FILE__).'/adapters/Adapter.php';
        $source  = dirname(__FILE__)."/adapters/$class.php";
        
        if(!file_exists($source))
        {
            throw new PipeException("$pipeclass not found!");
        }
        
        require_once($adclass);
        require_once($source);
        return $pipeclass;
    }
    
    public function __construct($info)
    {
        try
        {
            if($info->host[0] != '/')
            {
                $host = "host=$info->host";
                
                if(isset($info->port))
                {
                    $host .= "port=$info->port";
                }
            }
            else
            {
                $host = "unix_socket=$info->host";
            }
            
            $pdo = new PDO("$info->protocol:$host;dbname=$info->db", $info->user, $info->pass, self::$PDO_OPTIONS);
        }
        catch(PDOException $e)
        {
            throw new PipeException($e);
        }
        
        //Set connection
        $this->connection = $pdo;
    }
}

?>