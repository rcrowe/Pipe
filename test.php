<?php

require 'Pipe.php';

$pipe = Pipe::connect('localhost', 'root', 'root');
$users = $pipe->table('blunder', 'users');

// $users->select('id,name,age')->where('id', 2)->where('name', 'Rob')->like('%ob')->order_by('id')->limit(10)->offset(10)->get();

// $users->get_by_id(2);
// echo "\n";
$users->get_by_email('test@test.com');

if($users->exists())
{
    echo $users->count();
    
    echo $users->username;
    
    print_r($users->all);
}

?>