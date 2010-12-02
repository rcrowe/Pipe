<?php

require '../Pipe.php';

// Simple initialization
Pipe::initialize(function($cfg){
   
   $cfg->connection('mysql://root:root@localhost/pipe');
});


//Multiple connections
Pipe::initialize(function($cfg){
   
    $cfg->connections(array(
        'development' => 'mysql://root:root@localhost/pipe',
        'production'  => 'mysql://pipe:random@localhost/pipe'
    ));
    
    $cfg->connection('dev');
});

//Set connection based on environment
Pipe::initialize(function($cfg){
   
    $cfg->connections(array(
        'development' => 'mysql://root:root@localhost/pipe',
        'production'  => 'mysql://pipe:random@localhost/pipe'
    ));
    
    //Connection is set based on the environment
    //Either development or production
    
    //Check which environment your on
    if($cfg->environment() === 'development')
    {
        
    }
});

//Created and updated
Pipe::initialize(function($cfg){
   
   $cfg->created_field = 'created_on'; //default = created
   $cfg->updated_field = 'updated_on'; //default = updated
});

?>