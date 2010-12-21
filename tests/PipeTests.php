<?php

require_once 'simpletest/autorun.php';
require_once '../Pipe.php';
require_once './settings.php';

class PipeTests extends UnitTestCase
{
    //Check we can get an instance of Config returned in the closure
    function testInitialisePassedConfig()
    {
        //With PHP closures you cant pass in $this
        //So we will take a copy
        $simple = $this;
        
        //Lets check we are passed a config object so we can set some settings
        Pipe::initialise(function($cfg) use ($simple) {
            $simple->assertIsA($cfg, 'Pipe\Config');
            $cfg->connection(DSN);
        });
    }
    
    //Check we can set the DSN to connect to DB with
    function testSetDSN()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $dsn = Pipe\Config::instance()->connection();
        $this->assertIdentical($dsn, DSN);
    }
    
    //Test mysql:// is prepended if ommitted
    function testMySQLPrepended()
    {
        $subcon = substr(DSN, 8);
        
        Pipe::initialise(function($cfg){
           $cfg->connection($subcon);
        });
        
        $dsn = Pipe\Config::instance()->connection();
        $this->assertIdentical($dsn, DSN);
    }
    
    //Test we can see what environment we are running on
    //Useful for setting different DSNs
    function testEnvironment()
    {
        $simple = $this;
        
        Pipe::initialise(function($cfg) use ($simple) {
            $simple->assertTrue(($cfg->environment() === $cfg::DEVELOPMENT || $cfg->environment() === $cfg::PRODUCTION));
            $cfg->connection(DSN);
        });
    }
    
    //Test Created and Updated fields default
    function testFieldsDefault()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
        });
        
        $instance = Pipe\Config::instance();
        
        $this->assertIdentical($instance->created_field, 'created');
        $this->assertIdentical($instance->updated_field, 'updated');
    }
    
    //Test created field within config closure
    function testCreatedField()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
            $cfg->created_field = 'created_on';
        });
        
        $instance = Pipe\Config::instance();
        
        $this->assertIdentical($instance->created_field, 'created_on');
    }
    
    //Test updated field within config closure
    function testUpdatedField()
    {
        Pipe::initialise(function($cfg){
            $cfg->connection(DSN);
            $cfg->updated_field = 'updated_on';
        });
        
        $instance = Pipe\Config::instance();
        
        $this->assertIdentical($instance->updated_field, 'updated_on');
    }
    
    //Test just parsing in a connection string, not a closure. Makes things a lot simpler
    //@since 0.7.1
    function testInitialiseWithStringNotClosure()
    {
       Pipe::initialise(DSN);

       $dsn = Pipe\Config::instance()->connection();
       $this->assertIdentical($dsn, DSN);
    }
}

?>