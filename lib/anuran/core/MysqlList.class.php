<?php

require_once(PATH_CORE . '/MysqlIterator.class.php');

class DefaultList implements IteratorAggregate
{
    private $items  = array();

    public function add(stdClass $data){
        $this->items[]  = $data;
    }
    
    /**
     * @return MysqlIterator
     */
    public function getIterator(){
        return new MysqlIterator($this->items);
    }

    public function count(){
        return count($this->items);
    }
}

class MysqlList implements IteratorAggregate, IMysqlData
{
    protected $items  = array();

    /**
     * @var Mysql
     */
    protected $db;

    public function add(MysqlData $data){
        $this->items[]  = $data;
    }
    
    /**
     * @return MysqlIterator
     */
    public function getIterator(){
        return new MysqlIterator($this->items);
    }

    public function setDb( Mysql $db){
        $this->db   = $db;
    }

    public function count(){
        return count($this->items);
    }
    
    final public function save(Mysql $mysql = null){
        $mysql  = isset($mysql) ? $mysql : Mysql::instance();
        $mysql->save($this);
        return $this;
    }
    
    final public function insert(Mysql $mysql = null){
        $mysql  = isset($mysql) ? $mysql : Mysql::instance();
        $mysql->inserts($this);
        return $this;
    }
    
    final public function onsave(){
        foreach ($this as $data) {
        	$data->onsave();
        }
    }
    
    public function expectModify($count){
        if ($count == 0){
            MysqlException::expectModify();
        }
    }
    
    public function expectOneRecord($count){
        MysqlException::expectOneRecord($count);
    }
    
    public function onchange($key){
        foreach ($this as $item) {
        	$item->onchange($key);
        }
        return $this;
    }
    
    public function oninsert(){
        foreach ($this as $item) {
        	$item->oninsert($key);
        }
        return $this;
    }
    
    public function onload(){
        
    }
    
    public function oncreate(){
        
    }
    
    final public function getSqlConstructor(){
        return $this->current()->getSqlConstructor();
    }
    
    final public function current(){
        return $this->getIterator()->current();
    }
    
    final public function isClean(){
        foreach ($this as $item){
            if (!$item->isClean()) return false;
        }
        return true;
    }
    
    final public function isModify(){
        foreach ($this as $item){
            if ($item->isModify()) return true;
        }
        return false;
    }
    
    final public function isNew(){
        foreach ($this as $item) {
        	if (!$item->isNew()) return false;
        }
        return true;
    }
    
    final public function slice($index, $length){
        $this->items = array_slice($this->items, $index, $length);
        $this->count = count($this->items);
        return $this;
    }
    
    final public function shuffle(){
        shuffle($this->items);
    }
    
    final public function destroy(){
        foreach ($this as $item){
            $item->destroy();
        }
    }
}


?>