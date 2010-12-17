<?php

namespace Pipe;

require_once 'SQL.php';

use PDO;
use PDOException;

class Table
{
    private $config;
    private $connection;
    private $sql;
    
    private $table;
    private $columns;
    
    //Copy of result to check for a change against
    private $store;
    
    //Stores all results
    public $all = array();
    
    //Previous SQL statement
    public $lastSQL;

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
    
    //Return table name
    public function table()
    {
        return $this->table;
    }
    
    //Returns array of columns in specified table
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
    
    //Make a raw SQL query
    //You must manually handle the results, Pipe does not store them
    //@return PDOStatement
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
    
    //Clear any previous results
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
    
    //Stores result to test for a change against
    public function refresh_store()
    {
        foreach($this->columns as $field)
        {
            $this->store[$field] = $this->{$field};
        }
    }
    
    public function select($select)
    {
        $this->sql->select($select);
        return $this;
    }
    
    public function where($field, $value = null, $operator = '=', $type = 'AND')
    {
        $this->sql->where($field, $value, $operator, $type);
        return $this;
    }
    
    public function or_where($key, $value, $operator = '=')
    {
        $this->sql->or_where($key, $value, $operator);
        return $this;
    }
    
    public function like($field)
    {
        $this->sql->like($field);
        return $this;
    }
    
    public function order_by($field, $direction = 'ASC')
    {
        $this->sql->order_by($field, $direction);
        return $this;
    }
    
    public function limit($limit, $offset = 0)
    {
        //Make sure we arent passed NULL in
        $limit  = (is_null($limit)  || !is_int($limit)) ? 1 : $limit;
        $offset = (is_null($offset) || !is_int($limit)) ? 0 : $offset;
        
        $this->sql->limit($limit, $offset);
        return $this;
    }

    //Handles get_by_* calls
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
            
            return $this->get();
        }
    }
    
    //Only supports equal where statement
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
        //TODO: Need to put some checking in here, otherwise you can pass in a bad array format
        foreach($where as $field => $val)
        {
            $this->where($field, $val);
        }
        
        return $this->get();
    }

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
    
    //Returns the store of data. This is used to check for changes against
    //when making a save
    //Included here mainly for testing purposes
    public function store()
    {
        return $this->store;
    }
    
    public function count()
    {
        return count($this->all);
    }
    
    public function exists()
    {
        return (!empty($this->id));
    }
    
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
            return;
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