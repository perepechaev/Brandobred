<?php

class MysqlCondition
{

    const CONDITION_AND = 1;
    const CONDITION_OR  = 2;
    
    private $expression = self::CONDITION_AND;
    
    private $sql;
    
    /**
     * @var MysqlData
     */
    private $data;
    
    private $conditions = array();
    
    public function __construct(MysqlData $data){
        $this->data = $data;
    }
    
    private function checkConditions(){
        foreach ($this->conditions as $condition) {
            equal(is_object($condition), 'Параметром могут быть только объекты класса Condition: ' . var_export($condition, true));
        	equal($condition instanceof MysqlCondition, "'" . get_class($condition) . "' not instance of MysqlCondition");
        }
    }
    
    public function createAnd(){
        $condition  = clone $this;
        $condition->expression  = self::CONDITION_AND;
        $condition->conditions  = func_get_args();
        $this->checkConditions();
        return $condition;
    }
    
    public function createOr(){
        $condition  = clone $this;
        $condition->expression = self::CONDITION_OR;
        $condition->conditions  = func_get_args();
        $this->checkConditions();
        return $condition;
    }
    
    public function setDataObject(MysqlData $data){
        $this->data = $data;
    }
    
    private function createCondition($field, $value, $operator){
        $sql        = $this->data->getSqlConstructor()->getSqlField($field) 
                    . $operator 
                    . $this->data->getSqlConstructor()->getSqlValue($field, $value); 
        $condition  = clone $this;
        
        $condition->sql = $sql;
        return $condition;
    }
    
    public function equal($fieldName, $value, $negation = false){
        $operator   = $negation ? " <> " : " = ";
        return $this->createCondition($fieldName, $value, $operator);
    }
    
    public function isEqual($fieldName, $value, $negation = false){
        return is_null($value) ? clone $this : $this->equal($fieldName, $value, $negation); 
    }
    
    
    public function isNull($field_name, $value, $negation = false){
        if (is_null($value)){
            return clone $this;
        }
        $operator       = !$negation && $value ? " IS NULL " : " IS NOT NULL ";
        $result         = clone $this;
        $result->sql    = $this->data->getSqlConstructor()->getSqlField($field_name) . $operator;
        return $result;
    }
    
    public function letter($field, $value, $isEqual = false){
        return $this->createCondition($field, $value, $isEqual ? ' <= ' : ' < ');
    }
    
    public function isLetter($field, $value, $isEqual = false){
        return is_null($value) ? clone $this : $this->letter($field, $value, $isEqual);
    }
    
    public function greater($field, $value, $isEqual = false){
        return $this->createCondition($field, $value, $isEqual ? ' >= ' : ' > ');
    }
    
    public function isGreater($field, $value, $isEqual = false){
        return is_null($value) ? clone $this : $this->greater($field, $value, $isEqual);
    }
    
    public function like($field, $value, $pattern = '{like}%'){
        // Для использования масок (_%) в запросах LIKE просто закомменитровать 
        // нижеидущую строку (оставив при этом символ \\)
        $value = addcslashes($value, '_%\\');
        return $this->createCondition($field, str_replace('{like}', $value, $pattern), ' LIKE ');
    }
    
    public function isLike($field, $value, $pattern = '{like}%'){
        return is_null($value) ? clone $this : $this->like($field, $value, $pattern);
    }
    
    public function in($fieldName, $values, $negation = false){
        MysqlException::verificationConditionIn($values);
        
        $operator   = $negation === false ? " IN " : " NOT IN ";
        
        $callback   = array($this->data->getSqlConstructor(), 'getSqlValue');
        $fields     = array_fill(0, count($values), $fieldName);

        $condition  = clone $this;
        $condition->sql = $this->data->getSqlConstructor()->getSqlField($fieldName) . $operator . "(" . implode(',',  array_map($callback, $fields , $values)) . ")";
        return $condition;
    }
    
    public function isIn($fieldName, $values, $negation = false){
        if (empty($values)){
            return clone $this;
        }
        return $this->in($fieldName, $values, $negation);
    }
    
    public function execute(){
        if ($childrens  = $this->conditions){
            $sql    = array();
            foreach ($childrens as $key => $child) {
            	if (!is_null($conditionSql = $child->execute()) ){
            	    $sql[] = $conditionSql;
            	}
            }
            if ($sql){
                $operator  = $this->expression === self::CONDITION_AND ? ' AND ' : ' OR ';
                return "(" . implode($operator, $sql) . ")";
            }
            else{
                return null;
            }
        }
        
        return $this->sql;
    }
}

?>