Pipe
====

Pipe is a simple ORM for PHP that makes use of the ActiveRecord pattern. It was initially written for cases when you 
don't want to adhere to an MVC pattern with models, something that the current ORM solutions do not provide.

Pipe is built upon PDO which means it can be used across many different types of databases with the same API. Pipe currently
has adapters for MySQL, SQLite and PostgreSQL.

Requirements
------------

* [mandatory] PHP version 5.3 (developed using 5.3.1)

Pipe makes use of [PDO](http://php.net/manual/en/book.pdo.php) for querying the database.

Make sure you have the drivers available for your chosen database. Currently supported databases:

* MySQL
* SQLite
* PostgreSQL

Example
--------

    <?php
    
    require 'Pipe.php';
    
    Pipe::initialize(function($cfg){
       $cfg->connection('mysql://root:root@localhost/project');
    });
    
    //Select the `users` table
    $users = Pipe::table('users');
    
    //Select row `2` from `id` column
    //Short hand for: $users->select('*')->where('id', 2)->get()
    $users->get_by_id(2);
    
    //Change first name to Rob
    $users->first_name = 'Rob';
    
    //Make the change
    $users->save();
    
    ?>

Docs
----

Documentation is generated directly from the source code using PhpDocumentator.

To regenerate: rm -r docs && phpdoc -c phpdoc.ini

License
-------

Copyright 2010, Robert Crowe `hello@vivalacrowe.com`

Licensed under [The MIT License](http://www.opensource.org/licenses/mit-license.php)
Redistributions of files must retain the above copyright notice.
