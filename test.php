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
// $users->get_where(array(
    // 'last_name' => 'eas'
// ));

// if($users->exists())
// {
    // echo "First Name: ".$users->first_name."\n\n";
        
    // $users->first_name = 'cow';
    
    // $users->save();
    
    // echo "First Name: ".$users->first_name."\n\n";
// }


/* Example 3 */
//Create a new user

// $users->clear(); //best to make sure

// $users->email      = 'cow@moo.com';
// $users->username   = 'crab';
// $users->password   = sha1('sdfgmnk1234');
// $users->first_name = 'Alien';

// $users->save();

// echo "Last insert: ".$users->id;



//Test with new table
$errors = $pipe->table('blunder', 'errors');

$errors->project_id = 12;
$errors->hash       = sha1($errors->project_id);
$errors->parent_id  = 45;

$errors->save();

echo "ID: ".$errors->id;

?>