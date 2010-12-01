<?php

namespace Pipe;

/**
 * Gives the extending class the ability to add the singleton pattern
 */
abstract class Singleton {

    private static $instances = array();
    
    final public static function instance($name = null)
    {
        if(is_null($name))
        {
            $backtrace = debug_backtrace();
            
            //Set class to call
            $class_name = $backtrace[1]['class'];
            
            //Get the name of class
            list($package, $name) = explode('\\', $class_name);
            $name = strtolower($name);
        }
        else
        {
            $class_name = 'Pipe\\'.ucfirst($name);
        }
        
        if(!isset(self::$instances[$name]))
        {
            self::$instances[$name] = new $class_name;
        }
        
        //Return instance
        return self::$instances[$name];
    }
}