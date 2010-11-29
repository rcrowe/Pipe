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
    
    //Holds count of records from last request
    public $count;
    
    //Holds copy of existing data to check for changes against
    private $store;
    
    //Strings for building query
    private $select_str;
    private $where_str;
    private $like_str;
    private $orderby_str;
    private $limit_str;
    private $offset_str;

    public function __construct($db, $table, $fields)
    {
        $this->db     = $db;
        $this->table  = $table;
        $this->fields = $fields;
    }
    
    public function clear()
    {
        $this->all = array();
        
        $this->select_str = NULL;
        $this->where_str = NULL;
        $this->like_str = NULL;
        $this->orderby_str = NULL;
        $this->limit_str = NULL;
        $this->offset_str = NULL;
        
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
    
    public function where($field, $value = NULL, $type = 'AND')
    {
        if(strlen($this->where_str) > 0)
        {
            $this->where_str .= " $type ";
        }
        else
        {
            $this->where_str .= 'WHERE ';
        }
        
        if($value !== NULL)
        {
            $this->where_str .= "$field='$value'";
        }
        else
        {
            $this->where_str .= "$field";
        }
        
        //So we can chain
        return $this;
    }
    
    public function like($field)
    {
        $this->like_str = "LIKE '$field'";
        
        //So we can chain
        return $this;
    }
    
    public function order_by($field, $direction = 'ASC')
    {
        $dir = (strtoupper($direction) == 'ASC') ? 'ASC' : 'DESC';
        $this->orderby_str = "ORDER BY '$field' $dir";
        
        //So we can chain
        return $this;
    }
    
    public function limit($num)
    {
        $this->limit_str = "LIMIT '$num'";
        
        //So we can chain
        return $this;
    }
    
    public function offset($num)
    {
        $this->offset_str = "OFFSET '$num'";
        
        //So we can chain
        return $this;
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
    
    public function get_where($where, $limit = NULL, $offset = NULL)
    {
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
        //Build query
        $query = "SELECT ";
        
        //Handle SELECT
        $query .= (strlen($this->select_str) > 0) ? $this->select_str : '*';
        
        $query .= sprintf(" %s %s ", 'FROM', $this->table);
        
        //Handle WHERE
        $query .= (strlen($this->where_str) > 0) ? $this->where_str.' ' : '';
        
        //Handle LIKE
        $query .= (strlen($this->like_str) > 0) ? $this->like_str.' ' : '';
        
        //Handle ORDER BY
        $query .= (strlen($this->orderby_str) > 0) ? $this->orderby_str.' ' : '';
        
        //Handle LIMIT
        $query .= (strlen($this->limit_str) > 0) ? $this->limit_str.' ' : '';
        
        //Handle OFFSET
        $query .= (strlen($this->offset_str) > 0) ? $this->offset_str.' ' : '';
        
        
        /*
        Lets GO!
        */
        $result = @mysql_query($query);
        
        if(!$result)
        {
            throw new Exception("Error: ".mysql_error());
        }
        
        if(mysql_num_rows($result) > 0)
        {
            while($row = mysql_fetch_assoc($result))
            {
                $this->all[] = $row;
            }
            
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
        $colomns = array();
        $values  = array();
    
        if(!is_null($this->id))
        {
            //Get the data that has changed
            foreach($this->fields as $field)
            {
                if($this->{$field} !== $this->store[$field])
                {
                    $colomns[] = "$field";
                    $values[]  = $this->{$field};
                }
            }
            
            $col_str  = '('.implode(',', $colomns).')';
            $val_str  = '('.implode(',', $values).')';
            
            $query = sprintf("INSERT INTO %s %s VALUES %s", $this->table, $col_str, $val_str);
            
            echo $query;
            
            //lets GO!
            $result = @mysql_query($query);
            
            if(!$result)
            {
                throw new Exception("Error: ".mysql_error());
            }
            
            $this->refresh_store();
        }
    }
}

?>