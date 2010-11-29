<?php

require 'Pipe.php';

$pipe = Pipe::connect('localhost', 'root', 'root');
$users = $pipe->table('blunder', 'users');


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
// $errors = $pipe->table('blunder', 'errors');

// $errors->project_id = 12;
// $errors->hash       = sha1($errors->project_id);
// $errors->parent_id  = 45;

// $errors->save();

// echo "ID: ".$errors->id;



//Delete
// $users->get_by_id(8);

// $users->delete();


// $users->where('username', 'rrr')->or_where('username', 'crowe')->get();

// print_r($users->all);


//Testing updated field name
//defaults:
//  created_field = created
//  updated_field = updated

// $users->updated_field = 'last_login';

// $users->get_by_id(3);

// $users->email = 'test.test@test.com';

// $users->save();


//Test created field
// $users->created_field = 'created_on';

// $users->clear();

// $users->email = 'work@poo.com';

// $users->save();



//Now lets delete them
// $users->clear();
// $users->where('email')->like('work%')->get();
// $users->delete();

$sessions = $pipe->table('blunder', 'sessions');

$sessions->get();

print_r($sessions->all);

$sessions->delete();

?>