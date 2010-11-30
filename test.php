<?php

require 'Pipe.php';

//TODO: Allow to set other config options, thinking:
//  - created, updated fields
Pipe::initialize(function($cfg){

    // $cfg->connections(array(
        // 'development' => 'mysql://username:password@localhost/database_name'
    // ));
    
    // $cfg->connection('development'); //Select from set_connections()
    
    $cfg->connection('mysql://root:root@localhost/blunder'); //Use this connection, override anything else
});

$users = Pipe::table('users');

// $users->get_by_id(4);

// echo "ID: $users->username";

// $users->username = 'elliot';

// $users->save();

// echo "ID: $users->username";

// $users->get_by_id(5);

// $users->delete();

$users->select('id,username')->where('password', 'rrr')->limit(1)->get();

print_r($users->all);

?>