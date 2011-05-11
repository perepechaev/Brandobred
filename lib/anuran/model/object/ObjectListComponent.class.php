<?php

class ObjectListComponent extends MysqlList implements IteratorAggregate
{
    protected $count      = 0;
    
    private $withoutId    = false;

    /**
     *
     * @var Pager
     */
    public $pager;

    public function count(){
        return $this->count;
    }

    public function add(MysqlData $object){
        
        if (!$this->withoutId && $object->is_field('id') && $object->id > 0){
            equal((int) $object->id > 0, "Нет айдишника: " . var_export($object->id, true) . ' для объекта ' . get_class($object));
            $this->items[ $object->id ]  = $object;
        }
        else{
            $this->withoutId = true;
            $this->items[]  = $object;
        }

        $this->count++;
    }
    
    public function get($object_id){
        assert($this->withoutId === false);
        assert(isset($this->items[$object_id]));
//        assert($this->withoutId || !isset($this->items[$object_id]));
        return $this->items[$object_id];
    }
    
    public function is($object_id){
        return isset($this->items[$object_id]);
    }
    
    public function delete(MysqlData $object){
        assert($this->withoutId || isset($this->items[$object->id]));
        unset($this->items[$object->id]);
    }
    
    public function deleteById($object_id){
        assert($this->withoutId || isset($this->items[$object_id]));
        unset($this->items[$object_id]);
    }
    
    /**
     * @return MysqlIterator
     */
    public function getIterator() {
        return new MysqlIterator($this->items);
    }
    
    public function __get($field){
        if ($field === 'this') return $this;
        equal(false, $field);
    }
    
    /**
     * @param Pager $pager
     */
    public function setPager(IPager $pager){
        $this->pager = $pager;
    }
    
}