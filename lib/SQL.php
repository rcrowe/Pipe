<?php

namespace Pipe;

class SQL
{
    private $table;
    
    private $select_str;
    private $where_str;
    private $like_str;
    private $orderby_str;
    private $limit_offset_str;

    public function __construct($name)
    {
        $this->table = $name;
    }
    
    public function clear()
    {
        $this->select_str       = null;
        $this->where_str        = null;
        $this->like_str         = null;
        $this->orderby_str      = null;
        $this->limit_offset_str = null;
    }
    
    public function select($select)
    {
        if(is_array($select))
        {
            $select_str        = strtolower(implode(',', $select));
            
            $this->select_str .= $select_str;
        }
        else
        {
            //Make sure we select `ID` and make case lower
            $select = strtolower($select);
            
            $this->select_str .= $select;
        }
    }
    
    public function where($field, $value, $operator, $type)
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
            $this->where_str .= "$field$operator'$value'";
        }
        else
        {
            $this->where_str .= "$field";
        }
    }
    
    public function or_where($key, $value, $operator)
    {
        $this->where($key, $value, $operator, 'OR');
    }
    
    public function like($field)
    {
        $this->like_str = "LIKE '$field'";
    }
    
    public function order_by($field, $dir)
    {
        $dir = (strtoupper($dir) == 'ASC') ? 'ASC' : 'DESC';
        $this->orderby_str = "ORDER BY $field $dir";
    }
    
    public function limit($limit, $offset)
    {
        $this->limit_offset_str = "LIMIT $offset,$limit";
    }
    
    //Builds SQL string
    public function get()
    {
        $sql = "SELECT ";
        
        //Handle SELECT
        if(strlen($this->select_str) > 0)
        {
            //Make sure request includes ID, this is needed by Pipe
            //Dont think it matters if we have a duplicate
            $sql .= 'id,'.$this->select_str;
        }
        else
        {
            $sql .= '*';
        }
        
        $sql .= sprintf(" %s %s ", 'FROM', $this->table);
        
        //Handle WHERE
        $sql .= (strlen($this->where_str) > 0) ? $this->where_str.' ' : '';
        
        //Handle LIKE
        $sql .= (strlen($this->like_str) > 0) ? $this->like_str.' ' : '';
        
        //Handle ORDER BY
        $sql .= (strlen($this->orderby_str) > 0) ? $this->orderby_str.' ' : '';
        
        //Handle LIMIT & OFFSET
        $sql .= (strlen($this->limit_offset_str) > 0) ? $this->limit_offset_str.' ' : '';
        
        return trim($sql);
    }
}

?>