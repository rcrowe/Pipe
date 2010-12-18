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

/**
 * Builds the SQL statement for a get() request.
 *
 * @todo    Move all SQL out of Pipe\Table here
 * @package Pipe
 * @author  Robert Crowe <hello@vivalacrowe.com>
 */
class SQL
{
    /**
     * Selected table
     */
    private $table;
    
    /**
     * Holds SELECT part of statement
     */
    private $select_str;
    
    /**
     * Holds WHERE part of statement
     */
    private $where_str;
    
    /**
     * Holds LIKE part of statement
     */
    private $like_str;
    
    /**
     * Holds ORDER BY part of statement
     */
    private $orderby_str;
    
    /**
     * Holds LIMIT part of statement
     */
    private $limit_offset_str;

    /**
     * Pipe\SQL constructor
     *
     * @param string $name Name of table querying
     */
    public function __construct($name)
    {
        $this->table = $name;
    }
    
    /**
     * Clears currently built SQL statement
     */
    public function clear()
    {
        $this->select_str       = null;
        $this->where_str        = null;
        $this->like_str         = null;
        $this->orderby_str      = null;
        $this->limit_offset_str = null;
    }
    
    /**
     * Columns to select
     *
     * @param string|Array $select Columns to select
     */
    public function select($select)
    {
        if(is_array($select))
        {
            $select_str        = strtolower(implode(',', $select));
            
            $this->select_str .= $select_str;
        }
        else
        {
            $select = strtolower($select);
            
            $this->select_str .= $select;
        }
    }
    
    /**
     * Build WHERE statement
     *
     * @param string  $field    Field to select
     * @param various $value    Value to compare against
     * @param string  $operator Logical operator to use
     * @param string  $type     Joining string for multiple statements
     */
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
    
    /**
     * Builds SQL for shorthand method or_where. Wrapper around SQL::where()
     *
     * @param string  $field Field to select
     * @param various $value Value to compare against
     * @param string  $operator Logical operator to use
     */
    public function or_where($field, $value, $operator)
    {
        $this->where($field, $value, $operator, 'OR');
    }
    
    /**
     * Build LIKE statement
     *
     * @param string $field Use `%` for a wildcard
     */
    public function like($field)
    {
        $this->like_str = "LIKE '$field'";
    }
    
    /**
     * Build ORDER BY statement
     *
     * @param string $field Field to order by
     * @param string $dir   Direction to order by. Accepts ASC or DESC.
     */
    public function order_by($field, $dir)
    {
        $dir = (strtoupper($dir) == 'ASC') ? 'ASC' : 'DESC';
        $this->orderby_str = "ORDER BY $field $dir";
    }
    
    /**
     * Build LIMIT and offset statement
     *
     * @param int $limit  Number to limit by
     * @param int $offset Number of records to offset by
     */
    public function limit($limit, $offset)
    {
        $this->limit_offset_str = "LIMIT $offset,$limit";
    }
    
    /**
     * Generates the SQL. If get is only method called then will return all records.
     *
     * @return string
     */
    public function get()
    {
        $sql = "SELECT ";
        
        //Handle SELECT
        if(strlen($this->select_str) > 0)
        {
            //Make sure request includes ID, this is needed by Pipe
            //Dont think it matters if we have a duplicate?
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