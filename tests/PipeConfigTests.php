<?php

require_once 'simpletest/autorun.php';
require_once '../Pipe.php';

class PipeConfigTests extends UnitTestCase
{
    function setUp()
    {
        Pipe\Config::destroy();
    }

    //Check we can get an instance of Config
    function testGetInstance()
    {
        $instance = Pipe\Config::instance();
        $this->assertIsA($instance, 'Pipe\Config');
    }
    
    //Check its a singleton instance
    function testIsReallySingleton()
    {
        $instance_1 = Pipe\Config::instance();
        $instance_1->test = 'rcrowe';
        
        $instance_2 = Pipe\Config::instance();
        
        $this->assertIdentical($instance_2->test, 'rcrowe');
    }
    
    //Test we can destroy instance
    function testDestroy()
    {
        $instance_1 = Pipe\Config::instance();
        $instance_1->test = 'rcrowe';
        
        Pipe\Config::destroy();
        
        $instance_2 = Pipe\Config::instance();
        
        $this->assertFalse(isset($instance_2->test));
    }
    
    //Can set/get DSN
    function testSetDSN()
    {
        $instance = Pipe\Config::instance();
        $instance->connection('test');
        
        $this->assertIdentical($instance->connection(), 'mysql://test');
    }
    
    function testSetDSNAppendMYSQL()
    {
        $instance = Pipe\Config::instance();
        $instance->connection('root:root@localhost/pipe');
        
        $this->assertIdentical($instance->connection(), 'mysql://root:root@localhost/pipe');
    }
    
    //Returns a constant for the environment running under
    //Useful for setting different DSN
    function testEnvironment()
    {
        $instance = Pipe\Config::instance();
        
        $this->assertTrue(($instance->environment() === 0 || $instance->environment() === 1));
        $this->assertTrue(($instance->environment() === Pipe\Config::DEVELOPMENT 
                           || $instance->environment() === Pipe\Config::PRODUCTION));
    }
}

?>