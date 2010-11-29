<?php

class Pipe
{
    private $db; //MySQL connection

    private function __construct($db)
    {
        if(!$db)
        {
            throw new Exception("Make sure you pass a valid database resource in");
        }
        else
        {
            $this->db = $db;
        }
    }

    public static function connect($server, $user, $pass)
    {
        //Atempt to connect to MySQL db
        $db = @mysql_connect($server, $user, $pass);
        
        if(!$db)
        {
            throw new Exception("Error: ".mysql_error());
        }
        
        $pipe = new Pipe($db);
        
        return $pipe;
    }
    
    public function table($database, $table)
    {
        $result = @mysql_select_db($database, $this->db);
        
        if(!$result)
        {
            throw new Exception("Error: ".mysql_error());
        }
        
        $result = @mysql_query("SHOW COLUMNS FROM $table");
        
        if(!$result)
        {
            throw new Exception("Error: ".mysql_error());
        }
        
        //Get information on table
        $fields = array();
        
        if(mysql_num_rows($result) > 0)
        {
            while($row = mysql_fetch_assoc($result))
            {
                $fields[] = $row['Field'];
            }
            
            //Make sure it has an ID field
            if(!in_array('id', $fields))
            {
                throw new Exception("Table `$table` must contain an ID coloumn");
            }
        }
        else
        {
            throw new Exception('Unable to get information on table: '.$table);
        }
        
        //Return instance of table
        return new PipeTable($this->db, $table, $fields);
    }
}

class PipeTable
{
    private $db;     //DB instance
    private $table;  //Table name
    private $fields; //Fields table holds
    
    //Holds results
    public $all = array();
    
    //Holds copy of existing data to check for changes against
    private $store;
    
    //Strings for building query
    private $select_str;
    private $where_str;

    public function __construct($db, $table, $fields)
    {
        $this->db     = $db;
        $this->table  = $table;
        $this->fields = $fields;
    }
    
    public function clear()
    {
        $this->all = array();
        
        foreach($this->fields as $field)
        {
            $this->{$field} = NULL;
        }
        
        $this->refresh_store();
    }
    
    //Takes a copy of stored data to check for changes against it
    private function refresh_store()
    {
        foreach($this->fields as $field)
        {
            $this->store[$field] = $this->{$field};
        }
    }
    
    public function select($select)
    {
        if(is_array($select))
        {
            $this->select_str .= implode(',', $select);
        }
        else
        {
            $this->select_str .= $select;
        }
        
        //So we can chain
        return $this;
    }
    
    public function where($field, $value, $type = 'AND')
    {
        if(strlen($this->where_str) > 0)
        {
            $this->where_str .= $type.' ';
        }
        else
        {
            $this->where_str .= 'WHERE ';
        }
        
        $this->where_str .= "$field=$value";
        
        return $this;
    }
    
    public function get()
    {
        $this->clear();
        
        //Build query
        $query = "SELECT ";
        
        //Handle SELECT
        $query .= (strlen($this->select_str) > 0) ? $this->select_str : '*';
        
        $query .= sprintf(" %s %s ", 'FROM', $this->table);
        
        //Handle WHERE
        $query .= (strlen($this->where_str) > 0) ? $this->where_str : '';
        
        echo $query;
    }
}

?>