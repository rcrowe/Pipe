<?php

require '../Pipe.php';

// Set connection
Pipe::initialize(function($cfg){
   $cfg->connection('mysql://root:root@localhost/pipe');
});

//Set table
$users = Pipe::table('users');

// Select columns id, username and password
// Get all results
$users->select('id,username,password')->get();

// Or pass in an array
$users->select(array('id', 'username', 'password'))->get();

// Lets see the results
foreach($users->all as $user)
{
    echo 'ID: '.$user->id;
    echo 'ID: '.$user->username;
    echo 'ID: '.$user->password;
}

?>