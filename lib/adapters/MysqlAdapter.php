<?php

namespace Pipe;

class MysqlAdapter extends Adapter {

    public function column_info($table)
    {
        return $this->query("SHOW COLUMNS FROM $table");
    }
    
    public function limit($limit, $offset)
    {
        return "LIMIT $offset,$limit";
    }
}

?>