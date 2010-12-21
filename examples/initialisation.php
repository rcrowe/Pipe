<?php

//Include the Pipe library
require 'Pipe.php';

/* Basic */

//Pass in a DSN string
Pipe::initialise('user:table@host/table');

/* Advanced */

//It also support an anonymous function (closure)
//This gives you access to extra functions and helpers
Pipe:initialise(function($cfg){
    
});

//Currently only supports environment()
//This lets you know which environment your running on, either DEV or PROD
//Intend to expand this with more functionality
Pipe::initialise(function($cfg){
   
   if($cfg->environment() === Pipe\Config::DEVELOPMENT)
   {
       $cfg->connection('root:root@host/table');
   }
   else
   {
       'dbuser:random@host/table'
   }
   
});

?>