<?php

class Pipe
{
    private $db;               //MySQL connection
    private $table;            //Selected table
    public  $fields = array(); //List of fields for selected table
    
    //Used for build query string
    private $query_str;
    private $where_str;
    
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
        if(mysql_num_rows($result) > 0)
        {
            while($row = mysql_fetch_assoc($result))
            {
                $this->fields[] = $row['Field'];
            }
        }
        else
        {
            throw new Exception('Unable to get information on table: '.$table);
        }
        
        //Set table name using
        $this->table = $table;
    }
    
    //Handles get_by_* calls
    public function __call($method, $arguments)
    {
        if(strpos($method, 'get_by_') !== FALSE)
        {
            list($blank, $field) = explode('get_by_', $method);
            
            if(!in_array($field, $this->fields))
            {
                throw new Exception("Unable to find field: $field");
            }
            
            //Perform query
            if(isset($arguments[0]))
            {
                $this->where($field, $arguments[0], 'AND');
            }
            
            return $this->get(); 
        }
    }
    
    //Builds the WHERE section of query
    public function where($field, $value, $type)
    {
        if(strlen($this->where_str) > 0)
        {
            $this->where_str .= $type.' ';
        }
        else
        {
            $this->where_str .= "WHERE ";
        }
        
        $this->where_str .= "$field=$value";
    }
    
    public function get()
    {
        //Clear the object
        
        //Build query
        $query = "SELECT * FROM ".$this->table." ";
        
        if(strlen($this->where_str) > 0)
        {
            $query .= $this->where_str;
        }
        
        $result = @mysql_query($query);
        
        if(!$result)
        {
            throw new Exception("Error: ".mysql_error());
        }
        
        if(mysql_num_rows($result) > 0)
        {
            return mysql_fetch_assoc($result);
            // while($row = mysql_fetch_assoc($result))
            // {
                // $this->fields[] = $row['Field'];
            // }
        }
        
        /*
        $result = @mysql_query("SHOW COLUMNS FROM $table");
        
        if(!$result)
        {
            throw new Exception("Error: ".mysql_error());
        }
        
        //Get information on table
        if(mysql_num_rows($result) > 0)
        {
            while($row = mysql_fetch_assoc($result))
            {
                $this->fields[] = $row['Field'];
            }
        }
        */
    }
}

?>