<?php

require 'Pipe.php';

Pipe::initialize(function($cfg){

    // $cfg->connections(array(
        // 'development' => 'mysql://username:password@localhost/database_name'
    // ));
    
    // $cfg->connection('development'); //Select from set_connections()
    
    $cfg->connection('mysql://root:root@localhost/blunder'); //Use this connection, override anything else
});

$users = Pipe::table('users');

$users->name();

?>