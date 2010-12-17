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

use PDO;
use PDOException;

/**
 * Pipe Connection
 *
 * Takes the DSN and creates a PDO connection to the MySQL DB
 *
 * @package     Pipe
 * @author      Robert Crowe <hello@vivalacrowe.com>
 */
class Connection
{
    /**
     * Instance of Pipe\Connection
     *
     * @see Pipe\Connection::instance()
     */
    private static $instance;
    
    /**
     * PDO object used to query the database
     */
    public  $pdo;             //PDO instance
    
    /**
     * Instance of Pipe\Config
     */
    private $config;
    
    /**
     * Parsed DSN
     */
    private $dsn;

    /**
     * Options for PDO constructor
     */
    private $pdo_options = array(
        PDO::ATTR_CASE				=> PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE			=> PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS		=> PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES	=> false
    );

    private function __construct()
    {
        $this->config = Config::instance();
    }

    /**
     * Get a static instance of Pipe\Connection
     *
     * @return Pipe\Connection
     */
    public static function instance()
    {
        if(!isset(self::$instance))
        {
            self::$instance = new Connection;
        }

        return self::$instance;
    }

    /**
     * Destroy static instance of Pipe\Connection
     */
    public static function destroy()
    {
        self::$instance = null;
    }

    /**
     * Checks the DSN is valid and creates the PDO to the DB
     *
     * @throws PipeException
     * @throws PDOException
     */
    public static function initialise()
    {
        $instance = self::instance();

        //Make sure we have a DSN set
        if(is_null($instance->config->connection()))
        {
            throw new PipeException("Unable to connect to database. No DSN set");
        }

        //Parse DSN
        $instance->dsn = $instance->parseDSN($instance->config->connection());

        //Lets create an instance of PDO and connect to DB
        if($instance->dsn->host[0] != '/')
        {
            $host = 'host='.$instance->dsn->host;

            if(isset($instance->dsn->port))
            {
                $host .= 'port='.$instance->dsn->port;
            }
        }
        else
        {
            $host = 'unix_socket='.$instance->dsn->host;
        }

        $con = sprintf("%s:%s;dbname=%s", $instance->dsn->protocol, $host, $instance->dsn->db);

        //If cant connect to DB, PDO will throw a PDOException
        //Up to the user to handle this
        $instance->pdo = new PDO($con, $instance->dsn->user, $instance->dsn->pass, $instance->pdo_options);
    }

    /**
     * Returns the passed DSN
     *
     * @intenral Used for testing
     * @return Object
     */
    public function dsnInfo()
    {
        return $this->dsn;
    }

    /**
     * Parse the DSN into its specific parts
     *
     * @return Object
     */
    private function parseDSN($dsn)
    {
        //Lets parse and get the details needed
        $url = @parse_url($dsn);

        if(!isset($url['host']))
        {
            throw new PipeException('Database host must be specified in the connection string');
        }

        $info = new \stdClass();
        $info->protocol = $url['scheme'];
		$info->host		= $url['host'];
		$info->db		= isset($url['path']) ? substr($url['path'],1) : null;
		$info->user		= isset($url['user']) ? $url['user'] : null;
		$info->pass		= isset($url['pass']) ? $url['pass'] : null;

        if ($info->host == 'unix(')
        {
            $socket_database = $info->host . '/' . $info->db;

            if (preg_match_all('/^unix\((.+)\)\/(.+)$/', $socket_database, $matches) > 0)
            {
                $info->host = $matches[1][0];
                $info->db = $matches[2][0];
            }
        }

        if(isset($url['port']))
        {
            $info->port = $url['port'];
        }

        if (strpos($dsn,'decode=true') !== false)
        {
            if ($info->user)
            {
                $info->user = urldecode($info->user);
            }

            if ($info->pass)
            {
                $info->pass = urldecode($info->pass);
            }
        }

        return $info;
    }
}

 ?>