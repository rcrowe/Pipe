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

use Closure;

/**
 * Pipe Config
 *
 * Config object is responsible for storing all the configuration, connection and settings used through out Pipe.
 *
 * @package     Pipe
 * @author      Robert Crowe <hello@vivalacrowe.com>
 */
class Config extends Singleton {
    
    const DEVELOPMENT = 0;
    const PRODUCTION  = 1;

    /**
     * @var string Name of the database table accessing
     */
    public $table;
    
    /**
     * @var array Columns in the selected table
     */
    public $columns = array();
    
    /**
     * @var string Name of column to insert created timestamp.
     */
    public $created_field = 'created';
    
    /**
     * @var string Name of column to update timestamp
     */
    public $updated_field = 'updated';

    /**
     * @var Connection
     */
    public  $adapter = null;
    
    /**
     * @var string Connection to use if no default is set
     * @see environment()
     */
    private $default_connection = '';
    
    /**
     * @var string Either DSN to connect with, or the name of the connection to use
     */
    private $connection  = null;
    
    /**
     * @var array Stores multiple connections
     */
    private $connections = array();

    /**
     * Initialises Config. Sets the default connection @see self::default_connection.
     * Use Config::instance(), see Config::instance().
     */
    public function __construct()
    {
        //Work out the default connection based on environment
        //This for the default connection when set_connections() passes in an array of connections
        //and user hasnt manually selected the active one
        $default_connection = $this->environment();
        $this->default_connection = ($default_connection === self::DEVELOPMENT) ? 'development' : 'production';
    }

    /**
     * Sets up an instance of config passing in users settings
     *
     * @return void
     */
    public static function initialize(Closure $config)
    {
        // $config(self::instance('config'));
        $config(self::instance());
    }
    
    /**
     * Gets the current environment based on SERVER_NAME. 
     *
     * @return int Returns constant DEVELOPMENT or PRODUCTION.
     */
    public function environment()
    {
        $env = '';
        
        if(strpos($_SERVER['SERVER_NAME'], 'local.') !== FALSE OR $_SERVER['SERVER_NAME'] == 'localhost' 
                  OR strpos($_SERVER['SERVER_NAME'], '.local') !== FALSE)
        {
            $env = self::DEVELOPMENT;
        }
        else
        {
            $env = self::PRODUCTION;
        }
        
        return $env;
    }
    
    /**
     * Used to set one connection with a DSN or to select one connection when using multiple connections. Make sure
     * you are using a supported adapter and driver with your DSN.
     *
     * @param  string $name Sets the connection or if the name matches a multiple connection that connection is selected.
     * @return string If $name equals NULL, returns the currently set connection or connection name.
     */
    public function connection($name = null)
    {
        if(is_null($name))
        {
            return $this->connection;
        }
        else
        {
            if(array_key_exists($name, $this->connections))
            {
                $this->connection = $this->connections[$name];
            }
            else
            {
                //Else we have passed in a DSN
                $this->connection = $name;
            }
        }
    }
    
    /**
     * Allows you to set multiple connections.
     *
     * @see    connection
     * @param  array $connections Store multiple connections
     * @throws PipeException
     * @return string Currently set DSN or name of connection
     */
    public function connections($connections = null)
    {
        if(is_null($connections))
        {
            return $this->connections;
        }
        else
        {
            if(is_array($connections))
            {
                $this->connections = $connections;
            }
            else
            {
                throw new PipeException("Make sure you pass an associated array of connections or use connection() for a single connection");
            }
        }
    }
}

?>