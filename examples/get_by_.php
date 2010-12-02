<?php

require '../Pipe.php';

// Set connection
Pipe::initialize(function($cfg){
   $cfg->connection('mysql://root:root@localhost/pipe');
});

//Set table
$user = Pipe::table('users');

// get_by_ is a magic function that you can use to retrieve results based on select column
//It is equal to $users->select('*')->where('id', 2)->get();
$user->get_by_id(2);

echo $user->name;

//Or retrieve by email address
$user->get_by_email_addr('hello@vivalacrowe.com');

echo $user->email_addr;

?>