<?php

/**
 * Pipe
 *
 * A simple ORM without models
 *
 * @author      Robert Crowe <hello@vivalacrowe.com>
 * @link        http://vivalacrowe.com
 * @copyright   2010 Robert Crowe
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Pipe;

/**
 * Pipe Config
 *
 * Config object is responsible for storing all the configuration used to setup a connection to the database, as well as a few helpers.
 *
 * @package     Pipe
 * @author      Robert Crowe <hello@vivalacrowe.com>
 */
class Config
{
    /**
     * Name of column to insert created timestamp.
     */
    public $created_field = 'created';
    
    /**
     * Name of column to update timestamp
     */
    public $updated_field = 'updated';

    /**
     * Developement environment constant
     */
    const DEVELOPMENT = 0;
    
    /**
     * Production environment constant
     */
    const PRODUCTION  = 1;

    /**
     * Instance of Pipe\Config
     *
     * @see Pipe\Config::instance()
     */
    private static $instance;
    
    /**
     * DSN used to connect to database
     */
    private $dsn;
    
    /**
     * Pipe constructor. Not currently used
     */
    private function __construct(){}
    
    /**
     * Get a static instance of Pipe\Config
     *
     * @return Pipe\Config
     */
    public static function instance()
    {
        if(!isset(self::$instance))
        {
            //Doesnt already exist, create
            self::$instance = new Config;
        }
        
        return self::$instance;
    }
    
    /**
     * Destroy static instance of Pipe\Config. Make sure to call Pipe\Config::instance() for a new instance.
     *
     * @return void
     */
    public static function destroy()
    {
        self::$instance = null;
    }
    
    /**
     * Provides closure to main Pipe wrapper.
     *
     * @param Closure $config Closure with single parameter. Passed instance of Pipe\Config.
     * @return void
     */
    public static function initialise($config)
    {
        $instance = self::instance();
       
        $config($instance);
    }
    
    /**
     * Set DSN used to connect to database with. Appends mysql:// to DSN if omitted.
     *
     * @param string $dsn DSN to connect with
     * @return void
     */
    public function connection($dsn = null)
    {
        if(is_null($dsn))
        {
            return $this->dsn;
        }
        
        if(count(explode('mysql://', $dsn)) === 1)
        {
            $dsn = 'mysql://'.$dsn;
        }
        
        $this->dsn = $dsn;
    }
    
    /**
     * Environment currently running on. Helper function useful for setting different DSN strings.
     *
     * @see Pipe\Config::DEVELOPMENT
     * @see Pipe\Config::PRODUCTION
     * @return integer
     */
    public function environment()
    {
        $env = '';
        
        if(strpos($_SERVER['SERVER_NAME'], 'local.') !== FALSE || $_SERVER['SERVER_NAME'] == 'localhost' 
                  || strpos($_SERVER['SERVER_NAME'], '.local') !== FALSE)
        {
            $env = self::DEVELOPMENT;
        }
        else
        {
            $env = self::PRODUCTION;
        }
        
        return $env;
    }
}

?>