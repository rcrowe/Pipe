<?php

/**
 * Pipe
 *
 * A simple ORM without models
 *
 * @author      Robert Crowe <hello@vivalacrowe.com>
 * @link        http://vivalacrowe.com
 * @copyright   2010 Robert Crowe
 * @package     Pipe
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

require_once 'SQL.php';

use PDO;
use PDOException;

/**
 * Provides the ActiveRecord pattern for accessing and manipulating a table in the database.
 *
 * @package Pipe
 * @author  Robert Crowe <hello@vivalacrowe.com>
 */
class Table
{
    /**
     * Instance of Pipe\Config
     */
    private $config;
    
    /**
     * Instance of Pipe\Connection
     */
    private $connection;
    
    /**
     * Instance of Pipe\SQL
     */
    private $sql;
    
    /**
     * Name of table accessing
     */
    private $table;
    
    /**
     * Array of columns in the selected table
     */
    private $columns;
    
    /**
     * Copy of data is stored to check for a change against when making a save
     */
    private $store;
    
    /**
     * All the results returned from the query
     */
    public $all = array();
    
    /**
     * SQL from the last query
     *
     * @internal Mainly here for testing
     */
    public $lastSQL;

    /**
     * Table constructor. Returns instance of Pipe\Table to query against. If the table does not
     * exist a PDOException is thrown.
     *
     * @param  string $name Name of table
     * @throws PDOException
     * @throws PipeException
     * @return Pipe\Table
     */
    public function __construct($name)
    {
        if(!is_string($name) || strlen($name) === 0)
        {
            throw new PipeException("Invalid table name specified");
        }
        
        $this->table      = $name;
        $this->config     = Config::instance();
        $this->connection = Connection::instance()->pdo;
        $this->sql        = new SQL($name);
    
        //Check table exists
        //Do this trying to get column details
        $this->columns = $this->columns();
        
        //Make sure table has an `ID` field, Pipe needs this to work
        if(!in_array('id', $this->columns))
        {
            throw new PipeException("Table `$name` must contain an `id` column");
        }
    }
    
    /**
     * Table name
     *
     * @return string
     */
    public function table()
    {
        return $this->table;
    }

    /**
     * Columns in the table
     *
     * @throws PDOException
     * @throws PipeException
     * @return Array
     */
    public function columns()
    {
        $sth     = $this->query("SHOW COLUMNS FROM $this->table");
        $results = $sth->fetchAll();
        
        //Lets get the column names
        $cols = array();
        
        foreach($results as $row)
        {
            $cols[] = $row['field'];
        }
        
        return $cols;
    }
    
    /**
     * Make a raw SQL query
     *
     * @throws PDOException
     * @throws PipeException
     * @return PDOStatement
     */
    public function query($sql, $values = array())
    {
        //Record SQL statement
        $this->lastSQL = $sql;
    
        if(!$sth = $this->connection->prepare($sql))
        {
            throw new PipeException("Unable to perform command: $sql");
        }
        
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        
        if(!$sth->execute($values))
        {
            throw new PipeException("Unable to perform command: $sql");
        }
        
        return $sth;
    }
    
    /**
     * Clear any previous results and generated SQL. Important this is called before multiple calls.
     */
    public function clear()
    {
        $this->all = null;
        $this->sql->clear();
        
        foreach($this->columns as $field)
        {
            $this->{$field} = null;
        }
        
        $this->refresh_store();
    }
    
    /**
     * Store first result to test for a change against. Used when making a save.
     */
    private function refresh_store()
    {
        foreach($this->columns as $field)
        {
            $this->store[$field] = $this->{$field};
        }
    }
    
    /**
     * Build SELECT statement
     *
     * @param string|Array $select Columns to select
     * @return Pipe\Table Instance of table so calls can be chained
     */
    public function select($select)
    {
        $this->sql->select($select);
        return $this;
    }
    
    /**
     * Build WHERE statement
     *
     * @param string  $field    Field to select
     * @param various $value    Value to compare against
     * @param string  $operator Logical operator to use. Defaults to `=`
     * @param string  $type     Joining string for multiple statements. Defaults to `AND`
     * @return Pipe\Table Instance of table so calls can be chained
     */
    public function where($field, $value = null, $operator = '=', $type = 'AND')
    {
        $this->sql->where($field, $value, $operator, $type);
        return $this;
    }
    
    /**
     * Shorthand for OR WHERE. Wrapper around SQL::where()
     *
     * @param string  $field Field to select
     * @param various $value Value to compare against
     * @param string  $operator Logical operator to use
     * @return Pipe\Table Instance of table so calls can be chained
     */
    public function or_where($key, $value, $operator = '=')
    {
        $this->sql->or_where($key, $value, $operator);
        return $this;
    }
    
    /**
     * Build LIKE statement
     *
     * @param string $field Use `%` for a wildcard
     * @return Pipe\Table Instance of table so calls can be chained
     */
    public function like($field)
    {
        $this->sql->like($field);
        return $this;
    }
    
    /**
     * Build ORDER BY statement
     *
     * @param string $field Field to order by
     * @param string $dir   Direction to order by. Accepts ASC or DESC. Defaults to ASC
     * @return Pipe\Table Instance of table so calls can be chained
     */
    public function order_by($field, $direction = 'ASC')
    {
        $this->sql->order_by($field, $direction);
        return $this;
    }
    
    /**
     * Build LIMIT and offset statement
     *
     * @param int $limit  Number to limit by
     * @param int $offset Number of records to offset by. Defaults to 0
     * @return Pipe\Table Instance of table so calls can be chained
     */
    public function limit($limit, $offset = 0)
    {
        //Make sure we arent passed NULL in
        $limit  = (is_null($limit)  || !is_int($limit)) ? 1 : $limit;
        $offset = (is_null($offset) || !is_int($limit)) ? 0 : $offset;
        
        $this->sql->limit($limit, $offset);
        return $this;
    }

    //Handles get_by_* calls
    /**
     * Handles get_by_### calls. Append the name of column to the function, to return all the results.
     * Is a wrapper around the where(###, $value, '=')->get() call.
     *
     * @throws PDOException
     * @throws PipeException
     * @param various $args Value of the WHERE statement. 
     */
    public function __call($method, $args)
    {
        if(strpos($method, 'get_by_') !== FALSE)
        {
            list($blank, $field) = explode('get_by_', $method);
            
            if(!in_array($field, $this->columns))
            {
                throw new PipeException("Error using get_by_$field. Unable to find field: $field");
            }
            
            //Perform query
            if(isset($args[0]))
            {
                $this->where($field, $args[0], '=', 'AND');
            }
            
            $this->get();
        }
    }

    /**
     * Wrapper around get() and where()
     *
     * @param Array $where  Array of where statements. $key => $val. Only supports `=`
     * @param int   $limit  Number of records to limit by
     * @param int   $offset Number of records to offset by
     * @throws PDOException
     * @throws PipeException
     */
    public function get_where($where, $limit = NULL, $offset = NULL)
    {
        if(!is_array($where))
        {
            throw new PipeException('get_where only supports an array as its first argument');
        }
        
        if(!is_null($limit))
        {
            $this->limit($limit, $offset);
        }
        
        //Loop through each where item
        //TODO: Need to put some checking in here, otherwise you can pass in a bad array
        foreach($where as $field => $val)
        {
            $this->where($field, $val);
        }
        
        $this->get();
    }

    /**
     * Gets the records based on the previous functions called. If only get() is called
     * returns all records.
     *
     * @throws PDOException
     * @throws PipeException
     */
    public function get()
    {
        $sql = $this->sql->get();
        
        $sth = $this->query($sql);
        
        while($row = $sth->fetch(PDO::FETCH_ASSOC))
        {
            //Lets keep things as an object
            $obj = new \stdClass;
            
            //Add result to all
            foreach($row as $key => $val)
            {
                $obj->{$key} = $val;
            }
            
            $this->all[] = $obj;
        }
        
        if(count($this->all) > 0)
        {
            //Fill first result
            foreach($this->all[0] as $key => $value)
            {
                $this->{$key} = $value;
            }
        }
        
        $this->refresh_store();
    }
    
    /**
     * Returns current store. Used for checking for changes when a save is made.
     *
     * @internal Included here for testing
     * @see Pipe\Table::store
     * @return Array
     */
    public function store()
    {
        return $this->store;
    }
    
    /**
     * Number of records returned from the previous query
     *
     * @return int
     */
    public function count()
    {
        return count($this->all);
    }
    
    /**
     * Whether any records were returned from the last query
     *
     * @return bool
     */
    public function exists()
    {
        return (!empty($this->id));
    }
    
    /**
     * Either save a new record or update an existing record.
     *
     * @throws PDOException
     * @throws PipeException
     */
    public function save()
    {
        if(!is_null($this->id))
        {
            //Update
            $this->save_update();
        }
        else
        {
            //Insert
            $this->save_insert();
        }
    }

    /**
     * When an `ID` is present, the record is updated
     *
     * @see Pipe\Table::save()
     * @throws PDOException
     * @throws PipeException
     */
    private function save_update()
    {
        $set = array();
        
        //Get the data that changed
        foreach($this->columns as $field)
        {
            if($this->{$field} !== $this->store[$field])
            {
                $set[] = $field.'=\''.$this->{$field}.'\'';
            }
        }
        
        //Check we have something to change
        if(count($set) === 0)
        {
            return false;
        }
        
        //If table contains update_field, set update time
        if((strlen($this->config->updated_field) > 0) && in_array($this->config->updated_field, $this->columns))
        {
            $set[] = $this->config->updated_field.'='.time();
        }
        
        //Build query
        $set_str = implode(',', $set);
        $sql     = sprintf("UPDATE ".$this->table." SET $set_str WHERE id=".$this->id);
        
        //Lets Go!
        $sth = $this->query($sql);
        
        //Update store with new values
        $this->refresh_store();
    }

    /**
     * When an `ID` is not present, the record is created
     *
     * @see Pipe\Table::save()
     * @throws PDOException
     * @throws PipeException
     */
    private function save_insert()
    {
        $columns = array();
        $values  = array();
        
        //Get the data that has changed
        foreach($this->columns as $field)
        {
            if($this->{$field} !== $this->store[$field])
            {
                $columns[] = $field;
                $values[]  = '\''.$this->{$field}.'\'';
            }
        }
        
        //Check we have something to change
        if(count($columns) === 0)
        {
            return;
        }
        
        //If table contains update_field, set update time
        if((strlen($this->config->created_field) > 0) && in_array($this->config->created_field, $this->columns))
        {
            $columns[] = $this->config->created_field;
            $values[]  = time();
        }
        
        $col_str = sprintf("(%s)", implode(',', $columns));
        $val_str = sprintf("(%s)", implode(',', $values));
        
        $sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", $this->table, implode(',', $columns), implode(',', $values));
        
        //Lets Go!
        $sth = $this->query($sql);
        
        //Set ID from INSERT
        $this->id = $this->connection->lastInsertId();
        
        $this->refresh_store();
    }

    /**
     * When an `ID` is present, that record is deleted
     *
     * @throws PDOException
     * @throws PipeException
     */
    public function delete()
    {
        if(!is_null($this->id))
        {
            $sql = sprintf("DELETE FROM %s WHERE id=%s", $this->table, $this->id);
            $this->query($sql);
            $this->clear();
        }
    }
}

?>