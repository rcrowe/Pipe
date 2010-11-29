<?php

require 'Pipe.php';

$pipe = Pipe::connect('localhost', 'root', 'root');
$users = $pipe->table('blunder', 'users');

/*
OR
$db = mysql_connect('localhost', 'root', 'root');
$pipe = new Pipe($db);
$pipe->table('blunder', 'users');
*/


/* EXAMPLE 1 */
//Say you wanted to check if someone had already registered an email address, really simple with Pipe

// $users->get_by_email('nobby.crowe@gmail.com');

// if($users->count() > 0)
// {
    // echo 'Email already exists';
    // print_r($users->all);
// }
// else
// {
    // echo 'You can register this address';
// }

/* EXAMPLE 2 */
$users->get_where(array(
    'email' => 'test@test.com',
    'last_name' => 'eas'
));

if($users->exists())
{
    // print_r($users->all);
    
    echo "Email: ".$users->first_name."\n\n";
        
    $users->email    = 'robert.crowe@thalesgroup.com';
    $users->username = 'crowe';
    
    $users->save();
    
}

?>