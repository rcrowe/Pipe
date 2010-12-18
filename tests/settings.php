<?php

//Too many asserts fixed on the DSN below
//If this DSN is changed things are going to start to fail
//TODO: Fix this!


define('USERNAME', 'root');
define('PASSWORD', 'root');
define('HOST',     'localhost');
define('TABLE',    'PipeTest');


define('DSN', sprintf("mysql://%s:%s@%s/%s", USERNAME, PASSWORD, HOST, TABLE));

?>