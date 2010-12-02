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
    public $updated_field = 'updated';

    //PDO connection settings
    private $connection  = null;
    private $connections = array();
    public  $default_connection = '';
    public  $adapter = null;

    public function __construct()
    {
        //Work out the default connection based on environment
        //This for the default connection when set_connections() passes in an array of connections
        //and user hasnt manually selected the active one
        $this->default_connection = $this->environment();
    }

    public static function initialize(Closure $config)
    {
        // $config(self::instance('config'));
        $config(self::instance());
    }
    
    public function environment()
    {
        $env = '';
        
        if(strpos($_SERVER['SERVER_NAME'], 'local.') !== FALSE OR $_SERVER['SERVER_NAME'] == 'localhost' 
                  OR strpos($_SERVER['SERVER_NAME'], '.local') !== FALSE)
        {
            $env = 'development';
        }
        else
        {
            $env = 'production';
        }
        
        return $env;
    }
    
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