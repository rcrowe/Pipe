<?php

require_once 'simpletest/autorun.php';
require_once '../Pipe.php';
require_once './settings.php';

class PipeTableTests extends UnitTestCase
{
    function setUp()
    {
        Pipe\Config::destroy();
        Pipe\Connection::destroy();
    }
    
    function testInvalidTableName()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        try
        {
            Pipe::table('');
            $this->assertFalse(true, 'Expecting PipeException');
        }
        catch(Pipe\PipeException $e)
        {
            $this->assertTrue(true);
        }
        
        try
        {
            Pipe::table(0);
            $this->assertFalse(true, 'Expecting PipeException');
        }
        catch(Pipe\PipeException $e)
        {
            $this->assertTrue(true);
        }
    }
    
    //Test instance of Pipe\Table is returned
    function testTableReturned()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $this->assertIsA(Pipe::table('users'), 'Pipe\Table');
    }
    
    function testTableDoesntExist()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        try
        {
            Pipe::table('unknown');
            $this->assertFalse(true, 'Expected PDOException');
        }
        catch(PDOException $e)
        {
            $this->assertTrue(true);
        }
    }
    
    //Make sure exception thrown when table doesnt have an ID field
    function testIDCol()
    {
        $table = 'test_id_error';
    
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        try
        {
            Pipe::table($table);
            $this->assertFalse(true, 'Expected PipeException');
        }
        catch(Pipe\PipeException $e)
        {
            $this->assertIdentical($e->getMessage(), "Table `$table` must contain an `id` column");
        }
    }
    
    //Test SQL statement when looking up table column info
    function testColumnsSQL()
    {
        $table = "users";
        $sql   = "SHOW COLUMNS FROM $table";
        
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        //When getting an instance of a table, it queries details on the table
        $users = Pipe::table('users');
        
        $this->assertIdentical($users->lastSQL, $sql);
    }
    
    function testColumnsInfo()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $user = Pipe::table('users');
        
        $cols = $user->columns();
        
        $this->assertIdentical($cols[0], 'id');
        $this->assertIdentical($cols[1], 'username');
        $this->assertIdentical($cols[2], 'password');
        $this->assertIdentical($cols[3], 'first_name');
        $this->assertIdentical($cols[4], 'last_name');
    }
    
    function testQuery()
    {
        $sql = "SELECT id,first_name FROM users WHERE id=1";
    
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $users = Pipe::table('users');
        
        $result = $users->query($sql);
        
        //Get the result, only one row
        $row = $result->fetch();
        
        $this->assertIdentical($row['id'], (string)1);
        $this->assertIdentical($row['first_name'], "Rob");
        
        $this->assertIdentical($users->lastSQL, $sql);
    }
    
    //Test get() first as its the basis for a lot of other calls
    function testGet()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $users = Pipe::table('users');
        
        $users->get();
        
        $this->assertIdentical($users->lastSQL, "SELECT * FROM users");
        
        $this->assertIdentical($users->id, (string)1);
        $this->assertIdentical($users->username, "rcrowe");
        $this->assertIdentical($users->password, "test");
        $this->assertIdentical($users->first_name, "Rob");
        $this->assertIdentical($users->last_name, "Crowe");
    }
    
    function testSelectandClear()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $users = Pipe::table('users');
        
        //Test string param first
        $users->select('id,first_name')->get();
        
        //Check that it only contains specified columns
        //There is only one result in the users table
        $this->assertIdentical($users->id, (string)1);
        $this->assertIdentical($users->first_name, "Rob");
        
        //Make sure we dont have the other fields
        $this->assertFalse(isset($users->username));
        $this->assertFalse(isset($users->password));
        $this->assertFalse(isset($users->last_name));
        
        //Test array param
        
        //This is very important other wise SQL generated from the previous get() is used
        $users->clear();
        
        //Make sure clear works correctly
        //If it doesnt you will get a PDOException here
        try
        {
            $users->select(array('id', 'first_name'))->get();
            $this->assertTrue(true);
        }
        catch(PDOException $e)
        {
            $this->assertFalse(true, 'Pipe\Table::clear() has not worked correctly');
        }
        
        //Check that it only contains specified columns
        //There is only one result in the users table
        $this->assertIdentical($users->id, (string)1);
        $this->assertIdentical($users->first_name, "Rob");
        
        //Make sure we dont have the other fields
        $this->assertFalse(isset($users->username));
        $this->assertFalse(isset($users->password));
        $this->assertFalse(isset($users->last_name));
    }
    
    function testCount()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $where = Pipe::table('test_where');
        
        $where->get();
        
        $this->assertTrue($where->count() === 5);
    }
    
    function testWhereSingleEquals()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $where = Pipe::table('test_where');
        
        $where->where('where_name', 'name_2')->get();
        
        $this->assertIdentical($where->lastSQL, "SELECT * FROM test_where WHERE where_name='name_2'");
        
        $this->assertTrue($where->count() === 1);
        
        $this->assertIdentical($where->where_age, (string)34);
    }
    
    function testWhereOperatorLessThan()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $where = Pipe::table('test_where');
        
        $where->where('where_age', '21', '<=')->get();
        
        $this->assertIdentical($where->lastSQL, "SELECT * FROM test_where WHERE where_age<='21'");
        
        $this->assertTrue($where->count() === 3);
        
        $this->assertIdentical($where->all[0]->id, (string)1);
        $this->assertIdentical($where->all[1]->id, (string)3);
        $this->assertIdentical($where->all[2]->id, (string)4);
    }
    
    function testMultipleWhereDefault()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $where = Pipe::table('test_where');
        
        //Defaults to AND
        $where->where('where_age', '21', '<=')->where('where_age', '10', '>')->get();
        
        $this->assertIdentical($where->lastSQL, "SELECT * FROM test_where WHERE where_age<='21' AND where_age>'10'");
        
        $this->assertTrue($where->count() === 2);
        
        $this->assertIdentical($where->all[0]->id, (string)1);
        $this->assertIdentical($where->all[1]->id, (string)3);
    }
    
    function testMultipleWhereType()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $where = Pipe::table('test_where');
        
        //Defaults to AND
        $where->where('where_age', '21', '<=')->where('where_age', '10', '>', 'OR')->get();
        
        $this->assertIdentical($where->lastSQL, "SELECT * FROM test_where WHERE where_age<='21' OR where_age>'10'");
        
        $this->assertTrue($where->count() === 5);
    }
    
    //Test or_where, same as above just gives an easier to understand name
    function testMultipleWhereOr()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $where = Pipe::table('test_where');
        
        //Defaults to AND
        $where->where('where_age', '21', '<=')->or_where('where_age', '10')->get();
        
        $this->assertIdentical($where->lastSQL, "SELECT * FROM test_where WHERE where_age<='21' OR where_age='10'");
        
        $this->assertTrue($where->count() === 3);
    }
    
    function testLike()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $where = Pipe::table('test_where');
        
        $where->where('where_name')->like("%_3")->get();
        
        $this->assertIdentical($where->lastSQL, "SELECT * FROM test_where WHERE where_name LIKE '%_3'");
        
        $this->assertTrue($where->count() === 1);
        
        $this->assertIdentical($where->id, (string)3);
    }
    
    function testOrderBy()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $where = Pipe::table('test_where');
        
        //Order by default direction = ASC
        $where->order_by('where_age')->get();
        
        $this->assertIdentical($where->lastSQL, "SELECT * FROM test_where ORDER BY where_age ASC");
        
        $prev = 0;
        
        foreach($where->all as $result)
        {
            if($result->where_age >= $prev)
            {
                $this->assertTrue(true);
                $prev = $result->where_age;
            }
            else
            {
                $this->assertFalse(true, 'ORDER BY has not ordered correctly');
            }
        }
        
        //Order by default direction = DESC
        $where->clear(); //Make sure we clear if reusing table after a previous command
        
        $where->order_by('where_age', 'DESC')->get();
        
        $this->assertIdentical($where->lastSQL, "SELECT * FROM test_where ORDER BY where_age DESC");
        
        $prev = 100;
        
        foreach($where->all as $result)
        {
            if($result->where_age <= $prev)
            {
                $this->assertTrue(true);
                $prev = $result->where_age;
            }
            else
            {
                $this->assertFalse(true, 'ORDER BY has not ordered correctly');
            }
        }
        
        //Order by default direction = DESC
        //Make sure can handle lower case string
        $where->clear(); //Make sure we clear if reusing table after a previous command
        
        $where->order_by('where_age', 'desc')->get();
        
        $this->assertIdentical($where->lastSQL, "SELECT * FROM test_where ORDER BY where_age DESC");
        
        $prev = 100;
        
        foreach($where->all as $result)
        {
            if($result->where_age <= $prev)
            {
                $this->assertTrue(true);
                $prev = $result->where_age;
            }
            else
            {
                $this->assertFalse(true, 'ORDER BY has not ordered correctly');
            }
        }
        
        //Handles unknown direction
        //Order by default direction = DESC
        $where->clear();
        
        $where->order_by('where_age', 'unknown_string')->get();
        
        $this->assertIdentical($where->lastSQL, "SELECT * FROM test_where ORDER BY where_age DESC");
        
        $prev = 100;
        
        foreach($where->all as $result)
        {
            if($result->where_age <= $prev)
            {
                $this->assertTrue(true);
                $prev = $result->where_age;
            }
            else
            {
                $this->assertFalse(true, 'ORDER BY has not ordered correctly');
            }
        }
    }
    
    function testLimitAndOffset()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $where = Pipe::table('test_where');
        
        $where->where('where_age', '21', '<=')->limit(1)->order_by('where_age')->get();
        
        $this->assertTrue($where->count() === 1);
        
        $this->assertIdentical($where->id, (string)4);
        
        $where->clear();
        
        $where->where('where_age', '21', '<=')->limit(1,2)->order_by('where_age')->get();
        
        $this->assertTrue($where->count() === 1);
        
        $this->assertIdentical($where->id, (string)3);
    }
    
    function testGetByCalls()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $users = Pipe::table('users');
        
        $users->get_by_id(1);
        
        $this->assertIdentical($users->id, (string)1);
        $this->assertIdentical($users->username, "rcrowe");
        $this->assertIdentical($users->password, "test");
        $this->assertIdentical($users->first_name, "Rob");
        $this->assertIdentical($users->last_name, "Crowe");
        
        $users->clear();
        
        $users->get_by_first_name("Rob");
        
        $this->assertIdentical($users->id, (string)1);
        $this->assertIdentical($users->username, "rcrowe");
        $this->assertIdentical($users->password, "test");
        $this->assertIdentical($users->first_name, "Rob");
        $this->assertIdentical($users->last_name, "Crowe");
        
        //Make sure get_by_ can handle multiple results
        
        $where = Pipe::table('test_where');
        
        //Quick check to make sure we can chain before a get_by_ call
        $where->order_by('where_age')->get_by_where_age(34);
        
        $this->assertIdentical($where->lastSQL, "SELECT * FROM test_where WHERE where_age='34' ORDER BY where_age ASC");
        
        $this->assertTrue($where->count() === 2);
        
        $this->assertIdentical($where->all[0]->id, (string)2);
        $this->assertIdentical($where->all[0]->where_name, "name_2") ;
        $this->assertIdentical($where->all[0]->where_age, (string)34); 
        
        $this->assertIdentical($where->all[1]->id, (string)5);
        $this->assertIdentical($where->all[1]->where_name, "name_5"); 
        $this->assertIdentical($where->all[1]->where_age, (string)34);
    }
    
    function testGetWhere()
    {
        //Get only support an array as its param
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $where = Pipe::table('test_where');
        
        try
        {
            $where->get_where('test=test');
            $this->assertFalse(true, 'Expected Pipe\PipeException');
        }
        catch(Pipe\PipeException $e)
        {
            $this->assertIdentical($e->getMessage(), "get_where only supports an array as its first argument");
        }
        
        $where->get_where(array('where_age' => 34, 'where_name' => "name_5"));
        
        $this->assertIdentical($where->lastSQL, "SELECT * FROM test_where WHERE where_age='34' AND where_name='name_5'");
        
        $this->assertTrue($where->count() === 1);
        $this->assertIdentical($where->id, (string)5);
    }
    
    function testGetWhereLimitOffset()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $where = Pipe::table('test_where');
        
        //Make sure we get more than one result first
        $where->get_where(array('where_age' => 34));
        
        $this->assertTrue($where->count() > 1);
        
        $where->clear();
        
        $where->get_where(array('where_age' => 34), 1);
        
        $this->assertIdentical($where->lastSQL, "SELECT * FROM test_where WHERE where_age='34' LIMIT 0,1");
        
        $this->assertTrue($where->count() === 1);
        
        $this->assertIdentical($where->id, (string)2);
        $this->assertIdentical($where->where_name, "name_2");
        $this->assertIdentical($where->where_age, (string)34);
        
        $where->clear();
        
        $where->get_where(array('where_age' => 34), 1, 1);
        
        $this->assertIdentical($where->lastSQL, "SELECT * FROM test_where WHERE where_age='34' LIMIT 1,1");
        
        $this->assertTrue($where->count() === 1);
        
        $this->assertIdentical($where->id, (string)5);
        $this->assertIdentical($where->where_name, "name_5");
        $this->assertIdentical($where->where_age, (string)34);
    }
    
    //get() has pretty much been tested by this point
    //Only need to check refresh_store does its job
    function testGetRefreshStore()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $where = Pipe::table('users');
        
        //Make sure results are added to add
        $where->get_by_id(1);
        
        $this->assertIsA($where->store(), 'Array');
        
        $this->assertTrue(array_key_exists("id", $where->store()));
        $this->assertTrue(array_key_exists("username", $where->store()));
        $this->assertTrue(array_key_exists("password", $where->store()));
        $this->assertTrue(array_key_exists("first_name", $where->store()));
        $this->assertTrue(array_key_exists("last_name", $where->store()));
        
        $store = $where->store();
        
        $this->assertIdentical($store['id'], (string)1);
        $this->assertIdentical($store['username'], "rcrowe");
        $this->assertIdentical($store['password'], "test");
        $this->assertIdentical($store['first_name'], "Rob");
        $this->assertIdentical($store['last_name'], "Crowe");
    }
    
    //count() has been tested loads, not repeating
    
    function testExists()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $where = Pipe::table('users');
        
        $where->get_by_id(2);
        
        if($where->exists())
        {
            $this->assertFalse(true, 'No results should have been returned');
        }
        else
        {
            $this->assertTrue(true);
        }
        
        $where->clear();
        
        $where->get_by_id(1);
        
        if($where->exists())
        {
            $this->assertTrue(true);
        }
        else
        {
            $this->assertFalse(true, 'Expecting results to be returned');
        }
    }
    
    //An update works when you have retrieved a result
    //An ID must be present
    function testSaveUpdateNoUpdateField()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $where = Pipe::table('users');
        
        $where->get_by_id(1);
        
        $this->assertIdentical($where->first_name, "Rob");
        
        $where->first_name = "Elliot"; //Change value
        
        $where->save();
        
        //Lets make sure it changed
        
        $where->clear();
        
        $where->get_by_id(1);
        
        $this->assertIdentical($where->first_name, "Elliot");
        
        //Change the name back for future run throughs
        
        $where->first_name = "Rob";
        
        $where->save();
    }
    
    function testSaveInsertNoCreatedFieldAndDelete()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $user = Pipe::table('users');
        
        //If you previously retrieved any data eg: $where->get();
        //Make sure you have $where->cleared, otherwise row will be updated not inserted
        
        $user->username   = 'robcrowe';
        $user->password   = sha1('cow');
        $user->first_name = 'Viva';
        $user->last_name  = 'La';
        
        $user->save();
        
        //Make sure we have an ID attached now
        $this->assertTrue(isset($user->id));
        
        //Lets check data is entered correctly
        $id = $user->id;
        $user->clear();
        
        $user->get_by_id($id);
        
        $this->assertIdentical($user->id, (string)$id);
        $this->assertIdentical($user->password, sha1('cow'));
        $this->assertIdentical($user->username, 'robcrowe');
        $this->assertIdentical($user->first_name, 'Viva');
        $this->assertIdentical($user->last_name, 'La');
        
        //Warning this is destructive
        $user->delete();
        
        //Make sure row has been deleted
        $user->get_by_id($id);
        
        $this->assertFalse($user->exists());
    }
    
    //Test default updated field name
    function testSaveUpdatedWithField()
    {
        //Defaults to = updated
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $field = Pipe::table('test_save_fields_default');
        
        //Get value in table before attempting change
        
        $field->get_by_id(1);
        
        $updated = $field->updated;
        
        $field->name = 'name_changed';
        
        $field->save();
        
        
        //Check if created field changed
        $field->clear();
        
        $field->get_by_id(1);
        
        $new_updated = $field->updated;
                
        $this->assertIdentical($field->name, 'name_changed');
        $this->assertTrue($new_updated > $updated);
        
        
        //Put name back to default
        $field->clear();
        
        $field->get_by_id(1);
        
        $field->name = "test";
        
        $field->save();
    }
    
    //Test default created field name
    function testSaveCreatedWithField()
    {
        //Defaults to = updated
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $field = Pipe::table('test_save_fields_default');
        
        $field->name = 'new_name';
        
        $field->save();
        
        //Check created timestamp entered
        $id = $field->id;
        
        $field->clear();
        
        $field->get_by_id($id);
        
        $this->assertIdentical($field->name, "new_name");
        $this->assertTrue($field->created > 0);
        
        $field->delete();
    }
    
    //Test default updated field name
    function testSaveUpdatedWithCustomField()
    {
        // $custom_update_field = 'updated_on';
    
        //Defaults to = updated
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
            $cfg->updated_field = 'updated_on';
        });
        
        $field = Pipe::table('test_save_fields_custom');
                
        //Get value in table before attempting change
        
        $field->get_by_id(1);
        
        $updated = $field->updated_on;
        
        $field->name = 'name_changed';
        
        $field->save();
        
        
        //Check if created field changed
        $field->clear();
        
        $field->get_by_id(1);
        
        $new_updated = $field->updated_on;
                
        $this->assertIdentical($field->name, 'name_changed');
        $this->assertTrue($new_updated > $updated);
        
        
        // Put name back to default
        $field->clear();
        
        $field->get_by_id(1);
        
        $field->name = "test";
        
        $field->save();
    }
    
    //Test default created field name
    function testSaveCreatedWithCustomField()
    {
        //Defaults to = updated
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
            $cfg->created_field = 'created_on';
        });
        
        $field = Pipe::table('test_save_fields_custom');
        
        $field->name = 'new_name';
        
        $field->save();
        
        //Check created timestamp entered
        $id = $field->id;
        
        $field->clear();
        
        $field->get_by_id($id);
        
        $this->assertIdentical($field->name, "new_name");
        $this->assertTrue($field->created_on > 0);
        
        $field->delete();
    }
}

?>