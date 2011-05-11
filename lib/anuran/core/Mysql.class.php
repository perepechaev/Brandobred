<?php

require_once(PATH_CORE . '/MysqlException.class.php');

class Mysql
{
    static private $instance    = array();

    private $identify;
    private $user;
    private $pass;
    private $host;
    private $db;
    private $useDb      = false;

    private $link;
    private $result;
    private $last;
    
    private $countTrasaction = 0;
    
    public $logs;

    private $tablePrefix    = "";
    
    const FETCH_ARRAY       = 1;
    const FETCH_ASSOC       = 2;
    const FETCH_COLUMN      = 3;

    final private function __construct($id){
        $this->identify     = $id;
        $this->user         = MYSQL_USER;
        $this->pass         = MYSQL_PWD;
        $this->host         = MYSQL_HOST;
        $this->db           = (defined('TESTING_RUN') && TESTING_RUN) ? TEST_DBNAME : MYSQL_DBNAME;
        $this->tablePrefix  = MYSQL_TABLE_PREFIX;
        
        $this->link     = mysql_connect($this->host, $this->user, $this->pass);
        if (!$this->link){
            MysqlException::connectionFaile();
        }
        $this->query('SET NAMES '.MYSQL_ENCODE);

        try {
            $this->useDb();
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::DATABASE_NOT_FOUND ) throw $e;
        }
    }

    public function useDb(){
        $this->query('USE '.$this->db);
        $this->useDb    = true;
    }

    public function query($query){
        $this->last     = $query;
        $this->result   = mysql_query($this->last, $this->link);
        if (MYSQL_LOG) $this->logs[]   = $query;
        if (!$this->result){
            MysqlException::queryFailed($this->last);
        }
    }

    public function queryf($query, $args = array()){
        $this->query(vsprintf($query, $args));
    }
    
    protected function affected_rows(IMysqlData $data){
        $data->expectModify( mysql_affected_rows($this->link) );
    }

    public function select($query, MysqlData $data, $fields = false){
        $query  = $data->structure('prepareSqlQuery', $query);

        $default= $data->structure('getSqlFields', $this->tablePrefix);
        $fields = ($fields) ? $fields : $default;
        $fields = str_replace(':default:', $default, $fields);

        $query  = "SELECT $fields " .
                  "FROM ".$data->structure('getSqlTable', $this->tablePrefix) . " " .
                  $query;
        $this->query($query);
    }

    /**
     * XXX: Опять херня полная, либо убить, либо исправить.
     * 
     * @param $datas
     * @return unknown_type
     */
    public function selectRef($datas = array()){
        $fields = array();
        foreach ($datas as $data){
            assert($data instanceof MysqlData);
            $query      = $data->structure('prepareSqlQuery', $query);
            $fields[]   = $data->structrue('getSqlFields', $this->tablePrefix);
        }
        $fields = implode(', ', $fields);
    }

    public function last(){
        return $this->last;
    }

    public function fetch(MysqlData $data,MysqlList &$list = null){
        $list   = $data->createList();
        $cData  = get_class($data);
        while ($row = mysql_fetch_assoc($this->result)) {
            $oData  = clone $data;
            foreach ($row as $key => $value){
                $oData->structure('setRawValue', array($key, $value));
            }
            $oData->onload();
            $list->add($oData);
        }
        return $list;
    }
    
    public function fetchArray($fetchType = self::FETCH_ARRAY){
        $result = array();
        while ($row = mysql_fetch_assoc($this->result)) {
            switch ($fetchType){
                case self::FETCH_ARRAY:
                    $result[] = $row;
                    break;
                case self::FETCH_ASSOC:
                    $result[current($row)] = $row;
                    break;
                case self::FETCH_COLUMN:
                    $result[] = current($row);
                    break;
                default:
                    equal(false);
            }
        }
        
        return $result;
    }
    
    public function fetchArrayOne(){
        return mysql_fetch_assoc($this->result);
    }
    
    public function explain(){
        $res    = mysql_query('EXPLAIN '.$this->last(), $this->link);
        $ret    = array();
        if ($res){
            while ($row = mysql_fetch_assoc($res)) {
            	$ret[]   = $row;
            }
        }
        return $ret;
    }

    public function get(MysqlData &$data, $head = "", $foot = ''){
        $this->select($foot, $data, $head);
        $this->fetch($data, $list);
        if ($list->count() !== 1) {
            $data->expectOneRecord($list->count());
        }
        else{
            $data   = $list->getIterator()->current();
        }
    }
    
    public function getByCriteria(MysqlCriteria $criteria){
        $data   = $criteria->getData();
        $this->query($criteria->execute());
        $this->fetch($data, $list);
        if ($list->count() !== 1) {
            return $data->expectOneRecord($list->count());
        }
        else{
            $data   = $list->getIterator()->current();
        }
        return $data;
    }
    
    public function listByCriteria(MysqlCriteria $criteria){
        $this->query( $criteria->execute() );
        $this->fetch($criteria->getData(), $list);
        return $list;
    }
    
    public function getList(MysqlData $data, $head = '', $foot = ''){
        $this->select($foot, $data, $head);
        $this->fetch($data, $list);

        return $list;
    }
    
    public function isTableExists(MysqlData $data){
        $table  = $this->getTableName($data);
        $this->queryf("SHOW TABLES LIKE '%s'", array($table) );
        return mysql_num_rows($this->result);
    }

    public function getTableName(MysqlData $data){
        $table  = $this->tablePrefix . $data->structure('getTableName');
        return $table;
    }

    public function setTablePrefix($prefix){
        $this->tablePrefix  = $prefix;
    }
    
    public function getTablePrefix(){
        return $this->tablePrefix;
    }

    public function createTable(MysqlData $data){
        $fields = $data->getSqlConstructor()->getSqlCreateFields();
        
        $table  = $data->structure('getSqlTable', array($this->tablePrefix));

        $query  = "CREATE TABLE IF NOT EXISTS" . $table . "($fields) ENGINE=InnoDB DEFAULT CHARSET=".MYSQL_ENCODE;
        $this->query($query);
    }

    public function dropTable(MysqlData $data){
        $this->queryf("DROP TABLE %s", array( $data->structure('getSqlTable', $this->tablePrefix) ));
    }
    
    public function alterTable(IMysqlData $data){
        $fields     = $data->getSqlConstructor()->getFields();
        $columns    = $this->describeTable($data);
        $constr     = $data->getSqlConstructor();
        $uniques    = $constr->getUniqueFields();
        
        foreach ($fields as $field){
            if ($field == 'id') continue;
            if (isset($columns[$field])){
                $this->query( $constr->getSqlAlterField($field) );
            }
            else{
                $this->query( $constr->getSqlAlterAddField($field) );
            }
        }
        
        $keys       = $this->infoIndexTable($data);
        $indexs     = $constr->getIndexs();
        
        
        foreach ($keys as $key_name => $fields){
            $field = current($fields);
            if (array_search($key_name, $uniques) === false && array_search($key_name, $indexs) === false && $key_name !== 'PRIMARY'){
                $this->query('ALTER TABLE ' . $constr->getSqlTableName() . ' DROP INDEX ' . $key_name);
            }
        }
        
        $keys       = $this->infoIndexTable($data);
        foreach ($uniques as $unique){
            if (is_array($unique)){
                $index_name = implode('_', $unique);
            }
            else {
                $index_name = $unique;
            }
            if (!isset($keys[$index_name])){
                $this->query('ALTER TABLE ' . $constr->getSqlTableName() . ' ADD UNIQUE ' . $constr->getSqlCreateIndex($index_name));
            }
        }
        
        foreach ($indexs as $index){
            if (!isset($keys[$index])){
                $this->query('ALTER TABLE ' . $constr->getSqlTableName() . ' ADD INDEX ' . $constr->getSqlCreateIndex($index));
            }
        }
    }
    
    public function describeTable(IMysqlData $data){
        $this->query('DESCRIBE ' . $data->getSqlConstructor()->getSqlTableName());
        $result = array();
        while ($row = mysql_fetch_assoc($this->result)){
            $result[$row['Field']] = $row;
        }
        return $result;
    }
    
    private function infoIndexTable(IMysqlData $data){
        $this->query('SHOW INDEX FROM ' .$data->getSqlConstructor()->getSqlTableName() );
        $result = array();
        while ($row = mysql_fetch_assoc($this->result)){
            $result[$row['Key_name']][] = '`' . $row['Column_name'] . '`';
        }
        return $result;
    }
    

    public function insert(MysqlData &$data){
        $sql_fields = $data->getSqlConstructor()->getSqlFields($this->tablePrefix);
        $table_name = $data->getSqlConstructor()->getSqlTableName();
        
        $query  = "INSERT INTO {$table_name}
                  ({$sql_fields})
                  VALUES ({$data->structure('getSqlInsertValues')})
        ";

        $this->query($query);
        $fields = $data->structure('getFields');
        if (isset($fields['id']) && !empty($fields['id']['auto'])){
            $data->structure('setRawValue', array('id', mysql_insert_id($this->link)));
        };
        $this->affected_rows($data);
        $data->oninsert();
    }

    public function update(MysqlData $data){
        // NB: пользуемся пока только для элеметов имеющих id;
        equal($data->is_field('id'), 'извините... ');

        $table  = $data->getSqlConstructor()->getSqlTableName();
        $fields = $data->structure('getSqlUpdateFieldValues', array($this->tablePrefix));
        $id     = mysql_real_escape_string($data->id);

        $query  = "UPDATE $table SET $fields WHERE `id`=$id";
        $this->query($query);
        $this->affected_rows($data);        
    }
    
    public function updateByKey(MysqlData $data, $key = array()){
        $key = (array) $key;
        
        $table  = $data->structure('getSqlTable', $this->tablePrefix);
        $fields = $data->structure('getSqlUpdateFieldValues', $this->tablePrefix);
        $where  = "1 ";
        foreach ($key as $field => $value) {
            $where .= sprintf(" AND `%s`='%s'", 
                mysql_real_escape_string($field),
                mysql_real_escape_string($value)
            );
        }
        
        $query  = "UPDATE $table SET $fields WHERE $where";
        $this->query($query);
        $this->affected_rows($data);        
    }
    
    public function delete(MysqlData $data){
        // NB: Элемент обязан иметь id

        $table  = $data->getSqlConstructor()->getSqlTableName();
        $id     = mysql_real_escape_string($data->id);

        $query  = "DELETE FROM $table WHERE `id`=$id";
        $this->query($query);
        $this->affected_rows($data);
        $data->clean();
    }

    public function save(&$data){
        assert(is_object($data));

        if ($data instanceof MysqlList){
            try {
                $this->start_transaction();
                
                foreach ($data as $item){
                	assert($item instanceof MysqlData);
                	if ($item->isModify()){
                        $item->save();
                	}
                }
                
                $this->commit_transaction();
            }
            catch (Exception $e){
                $this->revert_transaction();
                throw $e;
            }
        }
        else{
            $data->prepare();
            
        	if ($data->is_field('id') && !is_null($data->id)){
                $this->update($data);
            }
            else {
                $this->insert($data);
            }
            $data->onsave();
        }
    }
    
    public function trySave(IMysqlData $data){
        try{
            $this->save($data);
            return true;
        }
        catch (MysqlException $e){
            if ($e->getCode() === MysqlException::DUPLICATE_ENTITY){
                return false;
            }
            throw $e;
        }
    }

    public function inserts(MysqlList $list){
        equal($list->count() > 0, 'Попытка вставить записи пустого списка');
        $data   = $list->getIterator()->current();
        $head   = "INSERT INTO {$data->structure('getSqlTable', $this->tablePrefix)}"
                . "({$data->structure('getSqlFields', $this->tablePrefix)}) VALUES ";
        $i      = 0;
        foreach ($list as $data){
        	assert($data instanceof MysqlData);
        	$data->prepare();
            $str[]  = "(" . $data->structure('getSqlInsertValues') . ")";

            if (++$i % MYSQL_COUNT_INSERT_LIST === 0) {
                $this->query( $head . implode(',', $str) );
                $this->affected_rows($list);                        
                $str    = array();
            }
        }

        if (!empty($str)){
            $this->query( $head . implode(',', $str) );
            $this->affected_rows($list);                    
        }
        
        $list->onsave();
    }
 
    public function getCountTransaction(){
        return $this->countTrasaction;
    }
    
    public function start_transaction(){
        $this->countTrasaction++; 
        
        if ($this->countTrasaction === 1){
            $query  = 'start transaction';
            $this->query($query);
        }
        elseif($this->countTrasaction < 1){
            MysqlException::transactionErrorCount();
        }
    }
    
    public function revert_transaction(Exception $e = null){
        $this->countTrasaction--;
        if ($this->countTrasaction === 0){
            $query  = 'rollback';
            $this->query($query);
        }
        elseif($this->countTrasaction < 0){
            $this->countTrasaction = 0;
            MysqlException::transactionErrorCount();
        }    
        else {
            MysqlException::transactionNestedRevert( $e );
        }
    }
    
    public function commit_transaction(){
        $this->countTrasaction--;
        if ($this->countTrasaction === 0){
            $query  = 'commit';
            $this->query($query);
        }
        elseif($this->countTrasaction < 0){
            $this->countTrasaction = 0;
            MysqlException::transactionErrorCount();
        }    
    }

    public function createDbIfNotExists(){
        $this->queryf('CREATE DATABASE IF NOT EXISTS %s', $this->db);
    }
    
    /**
     * @param string $identify Идентификатор соединения
     * @return Mysql
     */
    static public function instance($identify = 'default'){
        if (!isset(self::$instance[$identify])){
            self::$instance[$identify] = new Mysql($identify);
        }
        return self::$instance[$identify];
    }
}

?>