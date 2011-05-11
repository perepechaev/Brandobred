<?php

interface IMysqlData
{
    public function save(Mysql $mysql = null);
    public function insert(Mysql $mysql = null);
    public function onsave();
    public function oninsert();
    public function onchange($key);
    public function oncreate();
    public function onload();
    public function expectModify($count_modify_row);
    public function expectOneRecord($count);
    
    public function isClean();
    public function isModify();
    public function isNew();
    
    /**
     * @return MysqlConstructor
     */
    public function getSqlConstructor();
}

interface IListable{
    public function getAvailable();
    public function getDefault();
}

require_once(PATH_CORE . '/MysqlList.class.php');
require_once(PATH_CORE . '/MysqlConstructor.class.php');


abstract class MysqlData implements IMysqlData 
{
    private $fields = array();
    private $indexs = array();
    private $unique = array();

    private $refs   = array();
    private $alias  = array();

    private $table  = null;

    private $data   = array();
    
    /**
     * Флаго того, что объект чистый
     * @var string
     */
    private $clean  = true;
    
    /**
     * Флаг модифицированности объекта
     * @var string
     */
    private $modify = false;

    /**
     * @var ObjectComponent
     */
    protected $component    = null;
    
    /**
     * @var MysqlConstructor
     */
    private $sqlConstructor;

    abstract protected function make();

    final public function __construct($component = null){
        $this->table        = strtolower( get_class($this) );
        $this->component    = $component;
        $this->make();
        foreach ($this->fields as $name => $value) {
        	$this->data[$name] = null;
        }
        
        $this->sqlConstructor = new MysqlConstructor($this->table, $this->fields, $this->indexs, $this->alias, $this->unique);
        
        $this->oncreate();
    }
    
    public function __clone(){
        $this->oncreate();
    }
    

    public function __get($key){
        $key    = $this->isAlias($key) ? $this->getAlias($key) : $key;
        
        if (!isset($this->fields[$key])){
            if (isset($this->refs[$key])){
                return $this->refs[$key]->getValue();
            }
            elseif (method_exists($this, 'get'.$key)){
                if (empty($this->fields[$key])){
                    $this->fields[$key]['type'] = 'reference';
                } 
                $this->data[$key]   = call_user_func(array($this, 'get'.$key));
            }
            else {
                MysqlException::notFindFieldInStructure($this->getTableName(), $key);
            }
        }
        
        equal(array_key_exists($key, $this->data), "Поле '$key' не найдено для объекта " . get_class($this));
        equal(isset($this->fields[$key]), "Структура не обнаружена: $key\n" . var_export($this->fields, true));
        if ($this->fields[$key]['type'] == 'reference') {
            equal($this->data[$key] instanceof IReference, var_export($key, true));
            $result = $this->data[$key]->getValue();
        }
        else{
            $result = $this->data[$key];
        }
        
        return $result;
    }
    
    public function __set($key, $value){
        $this->setValue($key, $value);
        $this->onchange($key);
    }
    
    final public function is_field($key){
        return isset($this->fields[$key]);
    }
    
    final public function is_refs($key){
        return isset($this->refs[$key]);
    }
    
    final public function is_alias($key){
        return $this->isAlias($key);
    }

    final protected function name($tableName){
        $this->table    = $tableName;
    }

    /**
     * @return MysqlConstructor
     */
    final public function getSqlConstructor(){
        return $this->sqlConstructor;
    }

    
    final protected function field($name, $type, $extra = array()){
        equal(!isset($this->fields[$name]), "Попытка переопределить поле '$name', воспользуйтесь методом refield");
        $this->refield($name, $type, $extra);
    }
    
    final protected function enum($name, IListable $status, $params = array('request')){
        $params = array_merge(array(
            'default' => $status->getDefault(),
            'values'  => $status->getAvailable()
        ), $params);
        $this->field($name, 'enum', $params);
    }
    
    
    final protected function alias($name, $alias){
        $this->alias[$alias]    = $name;
    }
    
    final private function isAlias($name){
        return isset($this->alias[$name]);
    }
    
    final private function getAlias($alias){
        equal($this->isAlias($alias));
        return $this->alias[$alias];
    }
    
    final private function setAliasValue($alias, $value){
        $this->setRawValue( $this->getAlias($alias), $value );
    }
    
    final protected function refield($name, $type, $extra = array()){
        $this->fields[$name]            = array('type' => $type);
        $this->fields[$name]['request'] = false;
        
        if ( ($type === 'string') &&(!isset($extra['length']))){
            $extra['length'] = 255;
        }
        
        foreach ($extra as $key => $value){
            if (is_int($key)){
                $this->attachFieldParam($type, $value);
                $this->fields[$name][$value]    = true;
            }
            else {
                $this->attachFieldParam($type, $key);
                $this->fields[$name][$key]      = $value;
            }
            
        }
    } 

    final protected function reference($name, IReference $reference){
        assert(!isset($this->fields[$name]));
        $this->refs[$name]  = $reference;
    }

    final protected function index($fields){
        $indexes = func_get_args();
        foreach ($indexes as $fields){
            $this->indexs[] = (array) $fields;
        }
    }

    final protected function unique($fields){
        $unique = func_get_args();
        foreach ($unique as $fields){
            $this->unique[] = (array) $fields;
        }
    } 
    
    private function getFields(){
        return $this->fields;
    }
    
    private function getSqlCreateFields(){
        return $this->sqlConstructor->getSqlCreateFields();
    }

    private function getSqlFields($prefix){
        return $this->sqlConstructor->getSqlFields($prefix);
    }

    private function getValues(){
        return $this->data;
    }
    
    final public function getRawValues(){
        return $this->getValues();
    }
    
    private function setValue($name, $value){
        if (method_exists($this, 'set' . $name)){
            $value    = call_user_func(array($this, 'set' . $name), $value);
        }
        
        if ($this->isAlias($name)){
            $name   = $this->getAlias($name);
        }
        
        $this->modify   = true;
        $this->setRawValue($name, $value);
    }
    
    private function setRawValue($name, $value){
        if (!isset($this->fields[$name])){
            $reference  = ReferenceFactory::create($value);
            $this->reference($name, $reference);
        }
        else {
            if ($this->fields[$name]['type'] === 'int' && is_numeric($value)){
                $value = isset($this->fields[$name]['long']) ? $value : (int) $value;
            }
            $this->data[$name] = $value;
        }
        $this->clean    = false;
    }

    private function getSqlValues(){
        $result = array_merge($this->data, $this->refs);
        
        foreach ($result as $name => &$value){
            if (is_object($value) && $value instanceof IReference){
                if ($value instanceof StringReference){
                    $value  = "'". mysql_escape_string($value->__toString()) ."'";
                }
                else{
                    $value  = 'NULL';
                }
            }
            elseif ($this->fields[$name]['type'] !== 'int'){
                $value  = "'". mysql_escape_string($value) ."'";
            }
            elseif (!is_null($value) && !is_numeric($value)){
                MysqlException::wrongFieldValue($this->table, $name, $value);
            }
        }
        
        return $result;
    }

    private function getSqlValue($name){
        return $this->sqlConstructor->getSqlValue($name, $this->data[$name]);
    }

    /**
     * Возвращает для запроса Insert
     * строку с вставляемой записью
     *
     * @return string Строка для запроса в виде "value1, value2";
     */
    public function getSqlInsertValues(){
        $result = $this->data;
        foreach ($result as $name => &$value){
            if (!$value instanceof IReference){
                $value  = $this->sqlConstructor->getSqlValue($name, $value);
            }
        }
        return implode(',', $result);
    }

    private function getSqlUpdateFieldValues(){
        $result = $this->data;

        foreach ($result as $name => &$value){
            $value  = "`$name`=". $this->getSqlValue($name);
        }
        return implode(',', $result);
    }

    private function replaceSqlFields($var){
        return ":$var:";
    }


    private function getSqlTable($prefix = ""){
        return "`{$prefix}$this->table`";
    }

    private function getTableName(){
        return $this->table;
    }

    private function prepareSqlQuery($query){
        $values = $this->getSqlValues();
        $keys   = array_keys($values);
        $keys   = array_map(array($this, 'replaceSqlFields'), $keys);
        $values = array_values($values);

        $query  = str_replace($keys, $values, $query);
        return $query;
    }

    private function attachFieldParam($type, $param){
        $params = array(
            'int'       => array('request', 'unique', 'default', 'length', 'unsigned','auto', 'long'),
            'string'    => array('request', 'unique', 'default', 'length'),
            'text'      => array('request'),
            'date'      => array('request', 'unique'),
            'datetime'  => array('request'),
            'decimal'   => array('request', 'unique', 'default', 'length', 'unsigned','dec'),
            'enum'      => array('request', 'values', 'default')
        );
        if (!isset($params[$type]) || (array_search($param, $params[$type]) === false)){
            MysqlException::wrongFieldParam($this->table, $type, $param);
        }
    }

    /**
     * Доступ к приватным методам объекта
     *
     * Сделано для того, чтобы закрыть половину
     * публичных функций в private. Конечно,
     * теперь клиент сможет вызвать почти любой
     * метод этого класса, посмотрим как будет
     * это работать на практике
     * NB: уж очень не хочется создавать еще одну
     * проксю
     */
    public function structure($methodName, $param = array()){
        if (!method_exists($this, $methodName)){
            MysqlException::methodNotFound($this, $methodName);
        }
        return call_user_func_array(array($this, $methodName), $param);
    }

    /**
     * @return MysqlList
     */
    public function createList(){
        return new MysqlList();
    }
    
    /**
     * @return MysqlData
     */
    final public function save(Mysql $mysql = null){
        $mysql  = isset($mysql) ? $mysql : Mysql::instance();
        $mysql->save($this);
        $this->modify   = false;
        return $this;
    }
    
    final public function insert(Mysql $mysql = null){
        $mysql  = isset($mysql) ? $mysql : Mysql::instance();
        $mysql->insert($this);
        return $this;
    }
    
    final public function clean(){
        $class_name     = get_class($this);
        $clean_data     = new $class_name($this->component);
        
        $this->data     = $clean_data->data;
        $this->refs     = $clean_data->refs;
        $this->fields   = $clean_data->fields;
        $this->clean    = true;
        $this->modify   = false;
        
        $this->onclean();
    }
    
    public function expectModify($count){
        if ($count == 0){
            MysqlException::expectModify();
        }
    }
    
    public function expectOneRecord($count){
        MysqlException::expectOneRecord($count);
    }
    
    public function onclean(){
        return $this;
    }
    
    public function onchange($key){
        return $this;
    }
    
    /**
     * Метод вызывается перед операцией save()
     *
     * @return MysqlData
     */
    public function  prepare(){
    	return $this;
    }
    
    public function onsave(){
        return $this;
    }
    
    public function oninsert(){
        return $this;
    }
    
    /**
     * Метод вызывается при поднятии объекта из БД
     * @return MysqlData
     */
    public function onload(){
        foreach ($this->fields as $key => $params) {
        	if (!isset($this->data[$key])){
        	    $this->setRawValue($key, NULL);
        	}
        }
        $this->clean    = false;
        $this->modify   = false;
        return $this;
    }
    
    public function oncreate(){
        foreach ($this->fields as $key => $params) {
        	if (isset($params['default']) && empty($this->data[$key])){
        	    $this->setValue($key, $params['default']);
        	}
        }
        $this->clean    = true;
        $this->modify   = false;
        return $this;
    }
    
    /**
     * Проверить, есть ли у объекта несохраненные поля
     * 
     * @return unknown_type
     */
    public function isClean(){
        return $this->clean;
    }
    
    /**
     * Проверить, является ли Data-объект модифицированным 
     * после загрузки из БД
     * 
     * @see core/IMysqlData#isModify()
     */
    public function isModify(){
        return $this->modify;
    }
    
    /**
     * Объект еще не имеет записи в БД
     * 
     * @see core/IMysqlData#isNew()
     */
    public function isNew(){
        return is_null($this->data['id']);
    }
}

class MysqlDataCount extends MysqlData
{
    protected function make(){
        $this->field('count', 'int');
    } 
}

?>