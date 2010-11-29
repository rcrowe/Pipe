<?php

require 'Pipe.php';

$pipe = Pipe::connect('localhost', 'root', 'root');
$users = $pipe->table('blunder', 'users');

$users->select('id,name,age')->where('id', 2)->get();

// $users->get();

?>