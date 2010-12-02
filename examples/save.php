<?php

require '../Pipe.php';

// Set connection
Pipe::initialize(function($cfg){
   $cfg->connection('mysql://root:root@localhost/pipe');
});

//Set table
$user = Pipe::table('users');

//Lets create a user
$user->first_name = 'Rob';
$user->last_name  = 'Crow';

//Generates an INSERT SQL statement
$user->save();

//Users data is automatically loaded
//So you can now change their details
$user->last_name = 'Crowe';

//Generates an UPDATE SQL statement
$user->save();

?>