<?php

require_once 'Pipe.php';

// $pipe = new Pipe("mysql:dbname=blunder;host=localhost", 'root', 'root');
// $pipe->table('users');

$pipe = Pipe::connect('localhost', 'root', 'root');
$pipe->table('blunder', 'users');

// print_r($pipe->fields);

print_r($pipe->get_by_id(2));

?>