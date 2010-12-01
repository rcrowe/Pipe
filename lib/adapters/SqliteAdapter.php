<?php

namespace Pipe;

class SqliteAdapter extends Adapter {

    public function column_info($table)
    {
        return $this->query("pragma table_info($table)");
    }
    
    public function limit($limit, $offset)
    {
        return "LIMIT $offset,$limit";
    }
}

?>