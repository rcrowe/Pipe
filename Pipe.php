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

//Check we are running Pipe under the correct version of PHP
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300)
{
    die('Pipe requires PHP 5.3 or higher');
}

//Include Pipe library files
require 'lib/Exceptions.php';
require 'lib/Singleton.php';
require 'lib/Config.php';
require 'lib/Connection.php';
require 'lib/Table.php';

/**
 * Pipe
 *
 * Bootstraps the Pipe library. Sets the config and returns an instance of Table to query the database with.
 *
 * @package     Pipe
 * @author      Robert Crowe <hello@vivalacrowe.com>
 */
class Pipe {
    
    /**
     * Allows user to set Pipe settings
     *
     * @return void
     */
    public static function initialize($config)
    {
        Pipe\Config::initialize($config);
        Pipe\Connection::initialize();
    }
    
    /**
     * Returns an instance of Table to query against the selected table passed in as an argument.
     *
     * @param  string $name Table name to query against
     * @throws PipeException
     * @see    Table
     * @return Table
     */
    public static function table($name)
    {
        $config = Pipe\Singleton::instance('config');
        
        $sth     = $config->adapter->column_info($name);
        $results = $sth->fetchAll();
        
        //Lets fetch the columns in the table
        $cols = array();
        
        foreach($results as $row)
        {
            $cols[] = $row['field'];
        }
        
        //Make sure table has an ID field
        if(!in_array('id', $cols))
        {
            throw new PipeException("Table `$name` must contain an `id` column");
        }
        
        //Set config for table
        $config->table   = $name;
        $config->columns = $cols;
        
        return new Pipe\Table;
    }
}

?>