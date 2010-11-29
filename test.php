<?php

require_once 'Pipe.php';

$pipe = Pipe::connect('localhost', 'root', 'root');
$pipe->table('blunder', 'users');

// print_r($pipe->fields);

$pipe->get_by_id(2);

if($pipe->exists())
{
    echo $pipe->first_name;
}

?>