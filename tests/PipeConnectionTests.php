<?php

require_once 'simpletest/autorun.php';
require_once '../Pipe.php';
require_once './settings.php';

class PipeConnectionTests extends UnitTestCase
{
    function setUp()
    {
        Pipe\Config::destroy();
        Pipe\Connection::destroy();
    }

    //Check we can get an instance of Config
    function testGetInstance()
    {
        $instance = Pipe\Connection::instance();
        $this->assertIsA($instance, 'Pipe\Connection');
    }
    
    function testIsReallySingleton()
    {
        $instance_1 = Pipe\Connection::instance();
        $instance_1->test = 'rcrowe';
        
        $instance_2 = Pipe\Connection::instance();
        
        $this->assertIdentical($instance_2->test, 'rcrowe');
    }
    
    function testDestroy()
    {
        $instance_1 = Pipe\Connection::instance();
        $instance_1->test = 'rcrowe';
        
        Pipe\Connection::destroy();
        
        $instance_2 = Pipe\Connection::instance();
        
        $this->assertFalse(isset($instance_2->test));
    }
    
    function testNoDSNSet()
    {
        try
        {
            Pipe\Connection::initialise();
            $this->assertFalse(true, 'Expected to see PipeException');
        }
        catch(Pipe\PipeException $e)
        {
            $this->assertIdentical($e->getMessage(), 'Unable to connect to database. No DSN set');
        }
    }
    
    //No host set
    function testHostNotSet()
    {
        $dsn = 'mysql:///test';
        
        try
        {
            Pipe::initialise(function($cfg) use($dsn) {
            
                $cfg->connection($dsn);
            });
            
            $this->assertFalse(true, 'Expected to see a PipeException');
        }
        catch(Pipe\PipeException $e)
        {
            $this->assertIdentical($e->getMessage(), "Database host must be specified in the connection string");
        }
    }
    
    //This is going to need way more testing here than I've currently done
    //as there are so many variations
    //DSN parser does support definition of unix socket
    //And custom MySQL port
    function testParseDSN()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $instance = Pipe\Connection::instance();
        $info     = $instance->dsnInfo();
        
        $this->assertIdentical($info->protocol, 'mysql');
        $this->assertIdentical($info->host, 'localhost');
        $this->assertIdentical($info->user, 'root');
        $this->assertIdentical($info->pass, 'root');
        $this->assertIdentical($info->db, 'PipeTest');
    }
    
    //Test Exception is thrown when cant connect to a database
    //For what ever reason
    function testCantConnectException()
    {
        try
        {
            Pipe::initialise(function($cfg){
                $cfg->connection('mysql://rcrowe:blah_blah@localhost/what_ever');
            });
            
            $this->assertFalse(true, 'Expected PDOException');
        }
        catch(PDOException $e)
        {
            $this->assertTrue(true);
        }
    }
    
    //Test PDO instance is created
    function testPDOCreated()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $instance = Pipe\Connection::instance();
        
        $this->assertIsA($instance->pdo, 'PDO');
    }
}

?>