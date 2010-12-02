<?php

require 'Pipe.php';

//TODO: Allow to set other config options, thinking:
//  - created, updated fields
Pipe::initialize(function($cfg){

    //$cfg->connections(array(
    //    'dev'   => 'mysql://root:root@localhost/blunder',
    //    'test'  => ''
    //));
    
    //$cfg->connection('dev');
    
    $cfg->connection('mysql://root:root@localhost/blunder'); //Use this connection, override anything else
    
    //Or try this
    //either development or production
    //if($cfg->environment() === 'development')
    //{
    //    $cfg->connection('');
    //}
    
    //Default created field = created
    $cfg->created_field = 'created_on';
    
    //Default updated field = updated
    $cfg->updated_field = 'updated_on';
    
});


$users = Pipe::table('users');

// $users->get_by_id(4);

// echo "ID: $users->username";

// $users->username = 'elliot';

// $users->save();

// echo "ID: $users->username";

// $users->get_by_id(5);

// $users->delete();

//$users->select('id,username')->get();

//foreach($users->all as $user)
//{
//    echo $user->id;
//    echo $user->username;
//}

//$users->get_by_id(5);

/*$users->username = 'cowtest';
$users->email    = 'cow@test.com';
$users->password = sha1('el');

$users->save();*/

?>