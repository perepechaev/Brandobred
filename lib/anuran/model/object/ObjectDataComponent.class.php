<?php

class ObjectDataComponent extends MysqlData
{
    static protected $tableName   = 'object';

    /**
     * @var ObjectGiverComponent
     */
    protected $giver            = null;
    
    /**
     * @var ObjectAccessorComponent
     */
    private $accessor;

    protected function make(){
        $this->field('id',          'int',      array('request', 'unsigned', 'auto'));
        $this->field('title',       'string',   array('request'));
        $this->field('date',        'datetime', array('request'));
        $this->field('status',      'int',      array('default' => ObjectComponent::STATUS_UNPROVEN));
        $this->name(self::$tableName);

        equal(isset($this->component), 'Не выбрана компонента');
    }

    public function setGiver(ObjectGiverComponent $obj){
        $this->giver       = $obj;
        $this->giver->setData($this);
    }
    
    public function getGiver(){
        assert(isset($this->giver));
        return $this->giver;
    }
    
    final public function setAccessor(ObjectAccessorComponent $accessor){
        $this->accessor    = $accessor;
    }
    
    /**
     * @return ObjectAccessorComponent
     */
    public function getAccessor(){
        if (!isset($this->accessor)){
            equal(isset($this->component));
            return $this->component->getAccessor();
        }
        return $this->accessor;
    }

    public function __get($name){
        equal(isset($this->giver), 'Не установлен Giver-объект для класса '. get_class($this));
        if ($this->giver->_is($name)){
            return $this->giver->_get($name);
        }
        return parent::__get($name);
    }
    
    public function prepare(){
        if ($this->is_field('date') && is_null($this->date)){
            parent::__set('date', date('Y-m-d H:i:s'));
        }
        return parent::prepare();
    }
    
    /**
     * Обновить data-объект из БД
     *
     */
    final public function reload(){
        $criteria   = ObjectCriteriaComponent::create();
        $criteria->setStatus(array());
        $criteria->setId($this->id);
        
        $accessor = $this->getAccessor();
        $data   = $accessor->getByCriteria($criteria);
        
        foreach ($data->getRawValues() as $key => $value){
            $this->structure('setRawValue', array($key, $value));
        }
        $data->destroy();
        
        $this->onload();
    }

    public function __clone(){
        equal(is_object($this->giver), 'Не установлен Giver для ' . get_class($this) . ' объекта');
        $this->setGiver( clone $this->giver );
        return parent::__clone();
    }
    
    public function destroy(){
        $this->giver->destroy();
        unset($this->giver);
    }

    /**
     * @return ObjectListComponent
     */
    public function createList(){
        equal(isset($this->component), "Expect component: " . get_class($this));
        return $this->component->getList();
    }
    
    /**
     * @return ObjectComponent
     */
    public function getComponent(){
        return $this->component;
    }

}

?>