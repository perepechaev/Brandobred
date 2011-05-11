<?php
interface ICriteria
{
    public function execute();
    
    /**
     * @param $data
     * @return ICriteria
     */
    public function setData(MysqlData $data);
    
    public function setPage($page = 1);
    public function setPageCount($count);
    
    public function setOrder();
    
    public function setCriteriaHead();
    
    public function setCrtieraGroup();
}


class MysqlCriteria 
{
    const ORDER_ASC     = '+';
    
    const ORDER_DESC    = '-';
    
    private $head  = array();
    
    private $group = array();
    
    private $where = array();
    
    private $order = array();
    
    private $limit = array();
    
    private $join  = array();
    
    private $having = array();
    
    private $sqlWhere       = "";
    
    private $sqlOrder       = "";
    
    private $sqlGroup       = "";
    
    private $sqlJoin        = "";
    
    private $sqlJoinHead    = "";
    
    private $sqlHaving      = "";
    
    /**
     * @var MysqlData
     */
    private $data;
    
    /**
     * @var MysqlCondition
     */
    private $condition;
    
    /**
     * @param $data
     * @return MysqlCriteria
     */
    final public function setData(MysqlData $data){
        $this->data         = $data; 
        $this->setCondition( new MysqlCondition($this->data) );
        return $this;
    }
    
    final public function getData(){
        return $this->data;
    }
    
    final public function setCondition(MysqlCondition $condition){
        $this->condition    = $condition;
    }
    
    /**
     * Установить в запросе поля для получения. <p>
     * Например, требуется получить список id и title:
     * <code>
     *   $criteria = new ObjectCriteriaComponent();
     *   $criteria->setCriteriaSelect('id', 'title');
     *   $list = Mysql::instance()->listByCriteria( $criteria );
     * </code>
     * Выполняемый запрос:
     * <code>
     *   SELECT id, title FROM `table`
     * </code>
     */
    public function setCriteriaHead(){
        $this->head = func_get_args();
    }
    
    public function setCrtieraGroup(){
        $this->group = func_get_args();
    }
    
    public function setCriteriaHaving(){
        $this->having = func_get_args();
    }
    
    final public function join(MysqlData $data, $left_id, $right_id, $fields = array('*'),MysqlCondition $where = null){
        $this->join[] = array($data, $left_id, $right_id, (array) $fields, $where);
        if ($where){
            $this->where[] = $where;
        }
        return $this;
    }
    
    final protected function where(){
        $params = func_get_args();
        foreach ($params as $condition){
            equal($condition instanceof MysqlCondition, 'В метод where передан класс ' . get_class($condition));
            $this->where[]  = $condition;
        }
    }
    
    final protected function order(){
        $this->order = func_get_args();
    }
    
    final protected function header(){
        $this->head = func_get_args();
    }

    /**
     * 
     * @param $page     integer 
     * @param $count    integer
     * @return MysqlCriteria
     */
    final protected function limit($page = 1, $count = null){
        if ($count){
            $this->limit    = array(
                'start' => $count * (max($page - 1, 0)),
                'count' => $count
            );
        }
        return $this;
    }
    
    // Condition methods
    
    final protected function equal($fieldName, $value, $negation = false){
        return $this->getCondition()->equal($fieldName, $value, $negation);
    }
    
    final protected function isEqual($fieldName, $value, $negation = false){
        return $this->getCondition()->isEqual($fieldName, $value, $negation);
    }
    
    final protected function in($filedName, $values, $negation = false){
        return $this->getCondition()->in($filedName, $values, $negation);
    }
    
    final protected function isIn($fieldName, $values, $negation = false){
        if ($this->data->is_field($fieldName)){
            return $this->getCondition()->isIn($fieldName, $values, $negation);
        }
        else {
            return clone $this->getCondition();
        }
    }
    
    final protected function greater($field, $value, $isEqual = false){
        return $this->getCondition()->greater($field, $value, $isEqual);
    }
    
    final protected function isGreater($field, $value, $isEqual = false){
        return $this->getCondition()->isGreater($field, $value, $isEqual);
    }
    
    final protected function letter($field, $value, $isEqual = false){
        return $this->getCondition()->letter($field, $value, $isEqual);
    }
    
    final protected function isLetter($field, $value, $isEqual = false){
        return $this->getCondition()->isLetter($field, $value, $isEqual);
    }
    
    final protected function like($field, $value, $pattern = '{like}%'){
        return $this->getCondition()->like($field, $value, $pattern);
    }
    
    final protected function isLike($field, $value, $pattern = '{like}%'){
        return $this->getCondition()->isLike($field, $value, $pattern);
    }
    
    final protected function expAnd(){
        $params = func_get_args();
        return call_user_func_array(array($this->getCondition(), 'createAnd'), $params);
    }
    
    final protected function expOr(){
        $params = func_get_args();
        return call_user_func_array(array($this->getCondition(), 'createOr'), $params);
    }
    
    
    // Overwrites method
    
    protected function build(){
//        $constructor    = $this->data->getSqlConstructor();
    }
    
    private function itemOrder($order){
        $operator   = mb_substr($order, -1);
        $field      = mb_substr($order, 0, -1);
        
        if ($order === 'RAND()'){
            return 'RAND()';
        }
        
        switch ($operator) {
        	case self::ORDER_ASC:
        	   $operator   = 'ASC';
        	break;
        	
        	case self::ORDER_DESC:
        	   $operator   = 'DESC';
        	break;
        	
        	default :
        		equal(false, "Не выбран способ сортировки для поля '$order'. Используйте символы + и - в конце названия поля");
        	break;
        }
        
        return $this->getData()->getSqlConstructor()->getSqlField($field) . ' ' . $operator;
    }
    
    private function itemField($field){
        return $this->getData()->getSqlConstructor()->getSqlField($field);
    }
    
    private function buildWhere(){
        $where  = array();
        foreach ($this->where as $condtion){
            equal(is_object($condtion), 'where содержит не объект ');
            if (!is_null($sql = $condtion->execute())) $where[] = $sql;
        }
        $this->sqlWhere = $where ? " WHERE " . implode(' AND ', $where) : ""; 
    }
    
    private function buildJoin(){
        if (!$this->join){
            return false;
        }
        
        $previous = $this->data->getSqlConstructor();
        foreach ($this->join as $key => $table){
            $constructor    = $table[0]->getSqlConstructor();
            $this->sqlJoin .= ' LEFT JOIN ' . $constructor->getSqlTableName();
            $this->sqlJoin .= ' ON (' . $previous->getSqlField( $table[1] ) . ' = ' . $constructor->getSqlField( $table[2] ) . ')';
        }
    }
    
    private function buildJoinHead(){
        if (!$this->join){
            return false;
        }
        
        $head = array();
        foreach ($this->join as $table) {
        	$fields        = $table[3];
        	if (!$fields){
        	    continue;
        	}
        	
        	$constructor   = $table[0]->getSqlConstructor();
        	foreach ($fields as $key => &$field){
        	   if (is_int($key)){
                   $field = $constructor->getSqlField($field);
        	   }
        	   else {
                   $field = $constructor->getSqlField($key) . ' as ' . $field;
        	   }
            }
            $head[] =  implode(', ', $fields);
        }
        
        $this->sqlJoinHead = ", " . implode(', ', $head);
    }
    
    private function buildGroup(){
        if ($this->group){
            $this->sqlGroup = " GROUP BY " . implode(', ', array_map(array($this, 'itemField'), $this->group));
        }
    }
    
    private function buildHaving(){
        if ($this->having){
            $this->sqlHaving = ' HAVING ' . implode(' AND ', $this->having);
        }
    }
    
    private function buildOrder(){
        $order  = "";
        if ($this->order){
            $order  = " ORDER BY " . implode(', ', array_map(array($this, 'itemOrder'), $this->order));
        }
        $this->sqlOrder = $order;
    }
    
    private function buildHeader(){
        if (empty($this->head)){
            return $this->data->getSqlConstructor()->getSqlFields(Mysql::instance()->getTablePrefix());
        }
        else{
            $heads  = $this->head;
            foreach ($heads as $key => & $head) {
        	   $head = $this->data->getSqlConstructor()->getSqlField($head);
            }
            return implode(', ', $heads);
        }
    }
    
    final public function execute(){
        $criteria   = clone $this;
        $criteria->build();
        $criteria->buildJoin();
        $criteria->buildJoinHead();
        $criteria->buildWhere();
        $criteria->buildGroup();
        $criteria->buildHaving();
        $criteria->buildOrder();
        
        $limit  = ($criteria->limit) ? " LIMIT {$criteria->limit['start']}, {$criteria->limit['count']}" : "";
        
        $sql    = "SELECT " . $this->buildHeader() . $criteria->sqlJoinHead . " " 
                . "FROM " . $this->data->getSqlConstructor()->getSqlTableName()
                . $criteria->sqlJoin
                . $criteria->sqlWhere
                . $criteria->sqlGroup
                . $criteria->sqlHaving
                . $criteria->sqlOrder
                . $limit;
               
        return $sql;
    }
    
    final public function count(){
        $criteria   = clone $this;
        
        $criteria->build();
        $criteria->buildJoin();
        $criteria->buildJoinHead();
        $criteria->buildWhere();
        $criteria->buildGroup();
        $criteria->buildHaving();
        
        $sql    = "SELECT count(*) as count FROM " . $this->data->getSqlConstructor()->getSqlTableName() .
                   $criteria->sqlJoin .
                   $criteria->sqlGroup .
                   $criteria->sqlHaving .
                   $criteria->sqlWhere;
                   
        return $sql;
    }
    
    private function getCondition(){
        if (is_null($this->data))      MysqlException::dataNotInstance($this);
        if (is_null($this->condition)) MysqlException::conditionNotInstance($this);
        return $this->condition;
    }
}
?>