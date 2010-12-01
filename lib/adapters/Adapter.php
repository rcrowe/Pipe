<?php

namespace Pipe;

use PDO;
use PDOException;

abstract class Adapter extends Connection {

    abstract public function column_info($table);
    
    abstract public function limit($offset, $limit);
    
    public function query($sql, &$values = array())
    {
        try
        {
            if(!$sth = $this->connection->prepare($sql))
            {
                throw new PipeException("Unable to perform command: $sql");
            }
        }
        catch(PDOException $e)
        {
            throw new PipeException($e);
        }
        
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        
        try
        {
            if(!$sth->execute($values))
            {
                throw new PipeException("Unable to perform command: $sql");
            }
        }
        catch(PDOException $e)
        {
            throw new PipeException($e);
        }
        
        return $sth;
    }
}

?>