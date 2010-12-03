<?php

namespace Pipe;

require 'SQLBuilder.php';

use PDO;
use PDOException;

class Table extends Singleton {

    private $config;
    
    private $builder;
    
    public $all = array();
    
    private $store = array();

    public function __construct()
    {
        $this->config = self::instance('config');
        $this->builder = new SQLBuilder($this->config->table);
    }
    
    public function table()
    {
        return $this->config->table;
    }
    
    public function columns()
    {
        return $this->config->columns;
    }
    
    public function clear()
    {
        $this->all = null;
        
        $this->builder->clear();
        
        foreach($this->config->columns as $field)
        {
            $this->{$field} = null;
        }
        
        $this->refresh_store();
    }
    
    public function refresh_store()
    {
        foreach($this->config->columns as $field)
        {
            $this->store[$field] = $this->{$field};
        }
    }
    
    public function select($select)
    {
        $this->builder->select($select);
        return $this;
    }
    
    public function where($field, $value = null, $operator = '=', $type = 'AND')
    {
        $this->builder->where($field, $value, $operator, $type);
        return $this;
    }
    
    public function or_where($key, $value, $operator = '=')
    {
        $this->builder->or_where($key, $value, $operator);
        return $this;
    }
    
    public function like($field)
    {
        $this->builder->like($field);
        return $this;
    }
    
    public function order_by($field, $direction = 'ASC')
    {
        $this->builder->order_by($field, $direction);
        return $this;
    }
    
    public function limit($limit, $offset = 0)
    {
        $sql = $this->config->adapter->limit($limit, $offset);
        $this->builder->limit($sql);
        return $this;
    }
    
    //Handles get_by_* calls
    public function __call($method, $args)
    {
        if(strpos($method, 'get_by_') !== FALSE)
        {
            list($blank, $field) = explode('get_by_', $method);
            
            if(!in_array($field, $this->config->columns))
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
    
    public function get_where($where, $limit = NULL, $offset = NULL)
    {
        if(!is_array($where))
        {
            throw new PipeException('get_where only supports an array as its first argument');
        }
        
        if(!is_null($limit))
        {
            $this->limit($limit);
        }
        
        if(!is_null($offset))
        {
            $this->offset($offset);
        }
        
        //Loop through each where item
        foreach($where as $field => $val)
        {
            $this->where($field, $val);
        }
        
        return $this->get();
    }
    
    public function get()
    {
        $sql = $this->builder->get();
        
        $sth = $this->config->adapter->query($sql);
        
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
        foreach($this->config->columns as $field)
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
        if((strlen($this->config->updated_field) > 0) && in_array($this->config->updated_field, $this->config->columns))
        {
            $set[] = $this->config->updated_field.'='.time();
        }
        
        //Build query
        $set_str = implode(',', $set);
        $sql     = sprintf("UPDATE ".$this->config->table." SET $set_str WHERE id=".$this->id);
        
        //Lets Go!
        $sth = $this->config->adapter->query($sql);
        
        //Update store with new values
        $this->refresh_store();
    }
    
    private function save_insert()
    {
        $columns = array();
        $values  = array();
        
        //Get the data that has changed
        foreach($this->config->columns as $field)
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
        if((strlen($this->config->created_field) > 0) && in_array($this->config->created_field, $this->config->columns))
        {
            $columns[] = $this->config->created_field;
            $values[]  = time();
        }
        
        $col_str = sprintf("(%s)", implode(',', $columns));
        $val_str = sprintf("(%s)", implode(',', $values));
        
        $sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", $this->config->table, implode(',', $columns), implode(',', $values));
        
        //Lets Go!
        $sth = $this->config->adapter->query($sql);
        
        //Set ID from INSERT
        $this->id = $this->config->adapter->connection->lastInsertId();
        
        $this->refresh_store();
    }
    
    public function delete()
    {
        if(!is_null($this->id))
        {
            $sql = sprintf("DELETE FROM %s WHERE id=%s", $this->config->table, $this->id);
            $this->config->adapter->query($sql);
            $this->clear();
        }
    }
}

?>