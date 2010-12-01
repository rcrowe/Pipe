<?php

namespace Pipe;

class PgsqlAdapter extends Adapter {

    public function column_info($table)
    {
        $sql = <<<SQL
SELECT a.attname AS field, a.attlen,
REPLACE(pg_catalog.format_type(a.atttypid, a.atttypmod),'character varying','varchar') AS type,
a.attnotnull AS not_nullable, 
i.indisprimary as pk,
REGEXP_REPLACE(REGEXP_REPLACE(REGEXP_REPLACE(s.column_default,'::[a-z_ ]+',''),'\'$',''),'^\'','') AS default
FROM pg_catalog.pg_attribute a
LEFT JOIN pg_catalog.pg_class c ON(a.attrelid=c.oid)
LEFT JOIN pg_catalog.pg_index i ON(c.oid=i.indrelid AND a.attnum=any(i.indkey))
LEFT JOIN information_schema.columns s ON(s.table_name=? AND a.attname=s.column_name)
WHERE a.attrelid = (select c.oid from pg_catalog.pg_class c inner join pg_catalog.pg_namespace n on(n.oid=c.relnamespace) where c.relname=? and pg_catalog.pg_table_is_visible(c.oid))
AND a.attnum > 0
AND NOT a.attisdropped
ORDER BY a.attnum
SQL;
		$values = array($table,$table);
		return $this->query($sql,$values);
    }
    
    public function limit($limit, $offset)
    {
        return 'LIMIT '.intval($limit).' OFFSET '.intval($offset);
    }
}

?>