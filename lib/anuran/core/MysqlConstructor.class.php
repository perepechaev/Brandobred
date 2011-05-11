<?php
class MysqlConstructor
{
    
    private $fields;
    
    private $table;
    
    private $index;
    
    private $alias;
    
    private $unique;
    
    /**
     * @var MysqlData
     */
    private $data;
    
    public function __construct($table, array $fields = array(), $index = array(), $alias = array(), $unique = array()){
        $this->fields   = $fields;
        $this->table    = $table;
        $this->index    = $index;
        $this->alias    = $alias;
        $this->unique   = $unique;
    }
    
    public function getSqlValue($name, $value){
        $name = isset($this->alias[$name]) ? $this->alias[$name] : $name;
        
        if (is_object($value) && $value instanceof IReference){
            if ($value instanceof StringReference){
                $value  = "'". mysql_real_escape_string($value->__toString()) ."'";
            }
            else{
                $value  = 'NULL';
            }
        }
        elseif ((is_null($value) || ($this->fields[$name]['type'] === 'string' && empty($value))) && ($this->fields[$name]['request'] == true) && !(isset($this->fields[$name]['auto']) && $this->fields[$name]['auto'])){
            MysqlException::nullNotRequest($this->table, $name);
        }
        elseif ((!isset($value) && ($this->fields[$name]['request'] == false)) || (isset($this->fields[$name]['auto']) && $this->fields[$name]['auto'] && !isset($value))){
            $value  = 'NULL';
            return $value;
        }


        if (($this->fields[$name]['type'] === 'int') && !is_numeric($value)){
            MysqlException::wrongFieldValue($this->table, $name, $value);
        }

        if ($this->fields[$name]['type'] === 'string'){
            if (mb_strlen($value) > $this->fields[$name]['length']){
                MysqlException::wrongFieldLength($this->table, $name, mb_strlen($value));
            }
            $value  = "'". mysql_escape_string($value) ."'";
        }
        
        if ($this->fields[$name]['type'] === 'text'){
            $value  = "'". mysql_escape_string($value) ."'";
        }
        
        if ($this->fields[$name]['type'] === 'enum'){
            $value  = "'" . mysql_escape_string($value) . "'";
        }
        

        if ( ($this->fields[$name]['type'] === 'date')  && $value !== 'NULL' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)){
            MysqlException::wrongFieldValue($this->table, $name, $value);
        }
        elseif ($this->fields[$name]['type'] === 'date') {
            $value  = "'$value'";
        }

        if ( ($this->fields[$name]['type'] === 'datetime') && !preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $value)){
            MysqlException::wrongFieldValue($this->table, $name, $value);
        }
        elseif ($this->fields[$name]['type'] === 'datetime'){
            $value  = "'$value'";
        }

        if ( ($this->fields[$name]['type'] === 'decimal') && !is_numeric($value)){
            MysqlException::wrongFieldValue($this->table, $name, $value);
        }

        if (is_numeric($value) && isset($this->fields[$name]['unsigned']) && $this->fields[$name]['unsigned'] && $value < 0){
            MysqlException::wrongFieldValue($this->table, $name, $value);
        }

        return $value;
    }
    
    private function getSqlFieldDefinition($name, $field){
        $type   = $field['type'];
        $extra  = '';
        if ($type === 'string'){
            $length = isset($field['length']) ? $field['length'] : 255;
            $type   = ($length > 255) ? 'text' : "varchar($length)";
        }
        elseif ($type === 'string'){
            $length = '';
            $type   = 'text';
        }
        elseif ($type === 'int'){
            $length = isset($field['length']) ? $field['length'] : 11;
            $type   = isset($field['long']) ? "bigint($length)" : "int($length)";
            $extra  = isset($field['auto']) ? "$extra AUTO_INCREMENT PRIMARY KEY" : $extra;
            $field['request']   = isset($field['auto']) ? true : $field['request'];
        }
        elseif ($type === 'decimal'){
            $length = $field['length'] . ',' . $field['dec'];
            $type   = "decimal($length)";
        }
        elseif ($type === 'enum'){
            equal(!empty($field['values']) && is_array($field['values']), 'Using $this->field("field_name", "enum", array("values" => array("value1", "value2", "value3"))');
            $type   = "enum('" . (implode("','",  $field['values'])) . "')"; 
        }
//        $extra  = isset($field['unique'])   ? "$extra UNIQUE "   : $extra;
        $unsig  = isset($field['unsigned']) ? " UNSIGNED " : "";

        $null   = ($field['request']) ? "NOT NULL" : "NULL";
        $def    = (isset($field['default'])) ? "DEFAULT '".$field['default'] ."'" : "";

        return "`$name` $type {$unsig}$null $def $extra";
    }
    
    public function getSqlCreateFields(){
        $str    = array();
        
        foreach ($this->fields as $name => $field){
            $str[]  = $this->getSqlFieldDefinition($name, $field);
        }
        
        $str    = array_merge($str, $this->getCreateIndex());
        
        foreach ($this->fields as $name => $field){
            if (isset($field['unique'])){
                $str[]  = "UNIQUE $name ($name)";
            }
        }
        
        return "\t" . implode(",\n\t", $str);
    }
    
    private function getCreateIndex(){
        $str    = array();
        foreach ($this->index as $index){
            $name   = implode('_', $index);
            $str[]  = "INDEX `$name`(". implode(',', array_map(array($this, 'addThilde'), $index)) .")";
        }

        return $str;
    }
    
    private function addThilde($field){
        return "`$field`";
    }
    
    public function getSqlCreateIndex($index_name){
        foreach ($this->index as $index){
            $name   = implode('_', $index);
            if ($name === $index_name){
                return " `$name`(". implode(',', array_map(array($this, 'addThilde'), $index)) .")";
            }
        }
        
        foreach ($this->unique as $index){
            $name   = implode('_', $index);
            if ($name === $index_name){
                return " `$name`(". implode(',', array_map(array($this, 'addThilde'), $index)) .")";
            }
        }
        
        equal(false);
    }
    
    public function getSqlFields($prefix){
//        $prefix = Mysql::instance()->getTablePrefix();
        $aNames = array_keys($this->fields);
        $sNames = "`{$prefix}$this->table`.`" . implode("`, `{$prefix}$this->table`.`", $aNames) . "`";
        return $sNames;
    }

    public function getSqlField($fieldName){
        $prefix = '';
        $suffix = '';
        
        $this->getSqlFunctionHead($fieldName, $fieldName, $prefix, $suffix);
        
        if ($fieldName === '*'){
            return $prefix . $this->getSqlTableName() . ".*";
        }
        
        $fieldName = isset($this->alias[$fieldName]) ? $this->alias[$fieldName] : $fieldName;
        
        isset($this->fields[$fieldName]) or MysqlException::notFindFieldInStructure($this->table, $fieldName);
        return $prefix . $this->getSqlTableName() . ".`$fieldName`" . $suffix;
    }
    
    private function getSqlFunctionHead($function, &$fieldName, &$prefix, &$suffix){
        if (preg_match('/^(count|sum|max)\((DISTINCT)?\s?([\w\d_\.]{2,})\)\sas\s([\w\d]{2,})$/i', $function, $matches)){
            $fieldName  = $matches[3];
            $prefix     = $matches[1] . '(' . $matches[2] . ' ';
            $suffix     = ") as `{$matches[4]}`";
            return true;
        }
        
        if (preg_match('/^DISTINCT\s([\w\d_]{2,})$/', $function, $matches)){
            $fieldName  = $matches[1];
            $prefix     = 'DISTINCT ';
            return true;
        }
        return false;
    }
    
    public function getSqlTableName(){
        return "`" . Mysql::instance()->getTablePrefix() . "$this->table`";
    }
    
    public function getSqlAlterField($field){
        return "ALTER TABLE " . $this->getSqlTableName() . 
               " CHANGE COLUMN `$field`" .
               " " . $this->getSqlFieldDefinition($field, $this->fields[$field]);
    }
    
    public function getSqlAlterAddField($field){
        return "ALTER TABLE " . $this->getSqlTableName() . 
               " ADD COLUMN " . $this->getSqlFieldDefinition($field, $this->fields[$field]);
    }
    
    public function getFields(){
        return array_keys($this->fields);
    }
    
    public function getUniqueFields(){
        $result = array();
        foreach ($this->fields as $name => $field){
            if (isset($field['unique'])){
                $result[]   = $name;
            }
        }
        foreach ($this->unique as $uniq){
            $result[] = $uniq;
//            dd($this->unique);
        }
        
        return $result;
    }
    
    public function getIndexs(){
        $result = array();
        foreach ($this->index as $index){
            $result[] = implode('_', $index);
        }
        return $result;
    }
}
?>