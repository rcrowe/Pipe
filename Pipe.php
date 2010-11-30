<?php

//Check we are running Pipe under the correct version of PHP
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300)
{
    die('Pipe requires PHP 5.3 or higher');
}

require 'lib/Exceptions.php';
require 'lib/Singleton.php';
require 'lib/Config.php';
require 'lib/Connection.php';
require 'lib/Table.php';

class Pipe {
    
    public static function initialize($config)
    {
        Pipe\Config::initialize($config);
        Pipe\Connection::initialize();
    }
    
    public function table($name)
    {
        $config = Pipe\Singleton::instance('config');
        
        $sth     = $config->adapter->column_info($name);
        $results = $sth->fetchAll();
        
        //Lets fetch the columns in the table
        $cols = array();
        
        foreach($results as $row)
        {
            $cols[] = $row['field'];
        }
        
        //Make sure table has an ID field
        if(!in_array('id', $cols))
        {
            throw new PipeException("Table `$name` must contain an `id` column");
        }
        
        //Set config for table
        $config->table   = $name;
        $config->columns = $cols;
        
        return new Pipe\Table;
    }
    
    public static function environment()
    {
        $config = Pipe\Singleton::instance('config');
        
        return $config->default_connection;
    }
}

?>