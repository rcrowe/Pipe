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

class Pipe {
    
    public function initialize($config)
    {
        Pipe\Config::initialize($config);
        
        Pipe\Connection::initialize();
    }
}

?>