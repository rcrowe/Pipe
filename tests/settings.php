<?php

/**
 * Change your MySQL parameters below. Make sure you have set the database up with PipeTest.sql
 */

define('USERNAME', 'root');
define('PASSWORD', 'root');
define('HOST',     'localhost');
define('TABLE',    'PipeTest');

/* DO NOT CHANGE BELOW */

define('DSN', sprintf("mysql://%s:%s@%s/%s", USERNAME, PASSWORD, HOST, TABLE));

?>