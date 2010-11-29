<?php

// require_once 'Pipe.php';

// $pipe = Pipe::connect('localhost', 'root', 'root');
// $pipe->table('blunder', 'users');

// print_r($pipe->fields);

// $pipe->get_by_id(2);

// if($pipe->exists())
// {
    // echo $pipe->first_name;
// }



//Connect to DB
$pipe = Pipe::connect('localhost', 'root', 'root');
$users = $pipe->table('blunder', 'users');


//get_by_*
$users->get_by_id(2);

if($users->exists())
{
    $users->name;
}

//Delete
$users->where('id', 2)->get();
$users->delete();

//Save

//If ID is set, then UPDATE is issues
//else
//then INSERT

//Checks that field stored data doesnt equal current
//A clear will remove all this for you

$users->clear();

$users->name = "Hello World";

?>