<?php

require_once dirname(__FILE__) . '/ObjectComponent.class.php';

class ObjectAccessorComponent
{
    /**
     * @var ObjectComponent
     */
    protected $component        = null;
    
    /**
     * @var ObjectDataComponent
     */
    private $data;
    
    protected $limit            = null;

    final public function __construct(ObjectComponent $component = null){
        $this->component        = $component;
    }
    
    final public function getMysql(){
        return Mysql::instance();
    }
    
    /**
     * @return ObjectComponent
     */
    public function getComponent(){
        return $this->component;
    }
    
    /**
     * @return ObjectCriteriaComponent
     */
    public function getCriteria(){
        return new ObjectCriteriaComponent();
    }
    
    final public function setData(ObjectDataComponent $data){
        $this->data = $data;
        return $this;
    }
    
    public function getData(){
        if (!isset($this->data)){
            static $clone;
            $clone = $clone ? $clone : $this->component->getData();
            return $clone;
        }
        return $this->data;
    }

    /**
     * @param   int     $id
     * @return  ObjectDataComponent
     */
    final public function getById($id){
        $data   = $this->getData();
        
        if (empty($id)){
            $data->expectOneRecord(0);
            return $data;
        }
        
        $criteria   = $this->getCriteria();
        $criteria->setData( $data );
        $criteria->setId($id);
        
        return $this->getMysql()->getByCriteria( $criteria );
    }
    
    /**
     * Получить элемент по id игнорируя статус элемента
     * 
     * @param $id
     * @return ObjectDataComponent
     */
    final public function getForceById($id, $expect = true){
        $data   = $this->getData();
        
        if (empty($id)){
            $data->expectOneRecord(0);
            return $data;
        }
        
        $criteria   = $this->getCriteria();
        $criteria->setId($id);
        $criteria->setStatus( array() );
        $criteria->setData($data);
        
        try{
            return $this->getMysql()->getByCriteria( $criteria );
        }
        catch (MysqlException $e){
            if ($expect && $e->getCode() === MysqlException::EXPECT_ONE_RECORD) throw $e;
            if (!$expect){
                return false;
            }
            throw $e; 
        }
    }
    
    final public function getByCriteria(ICriteria  $criteria){
        $data       = $this->getData();
        $criteria->setData( $data );
        return $this->getMysql()->getByCriteria( $criteria );
    }
    
    /**
     * @param ObjectCriteriaComponent $criteria
     * @return MysqlList
     */
    final public function listByCriteria(ICriteria $criteria, IPager $pager = null){
        $data       = $this->getData();
        $criteria->setData($data);
        
        
        // Если isset($pager), то применяем лимит и наполняем лист
        if (isset($pager) && $pager->isVisible()){
            $pager->setCountPage( $this->getCountByCriteria( $criteria ) );
            $criteria->setPageCount( $pager->countPage );
            $criteria->setPage( $pager->page );
            $pager->init();
        }
            
        $list = $this->getMysql()->listByCriteria($criteria);
        
        if (isset($pager)){
            $list->setPager($pager);
        }
        
        return $list;
    }
    
    /**
     * Получить список id по критерии
     * 
     * @param $criteria ICriteria
     * @return array
     */
    final public function arrayByCriteria(ICriteria $criteria, $type = Mysql::FETCH_ARRAY){
        $data       = $this->getData();
        $criteria->setData($data);
        
        $this->getMysql()->query( $criteria->execute() );
        
        return $this->getMysql()->fetchArray( $type );
    }
    
    final public function getCountByCriteria(ICriteria $criteria){
        $data       = $this->getData();
        $criteria->setData($data);
        
        $result     = new MysqlDataCount();
        $this->getMysql()->query($criteria->count());
        $this->getMysql()->fetch($result, $list);
        
        $count      = (int) $list->current()->count;
        return $count;
    }
    
    /**
     * @param $aggregator
     * @param $criteria
     * @return IMysqlData
     */
    final public function getAggreageteByCriteria(IMysqlData $aggregator, ICriteria $criteria){
        $data       = $this->getData();
        $criteria->setData($data);
        
        $this->getMysql()->query($criteria->execute());
        $this->getMysql()->fetch($aggregator, $list);
        equal($list->count() < 2, $list->count());
        
        return $list->current();
    }
    
    final public function listAggreageteByCriteria(IMysqlData $aggregator, ICriteria $criteria){
        $data       = $this->getData();
        $criteria->setData($data);
        
        $this->getMysql()->query($criteria->execute());
        $this->getMysql()->fetch($aggregator, $list);
        
        return $list;
    }
    
    /**
     * @param str $title
     * @return ObjectDataComponent
     */
    final public function getByTitle($title, $only_approve = true){
        $data           = $this->getData();
        
        $criteria       = $this->getCriteria();
        $criteria->setData($data);
        $criteria->setTitle( $title );
        $criteria->setStatus($only_approve ? ObjectComponent::STATUS_APPROVE : null);
        $criteria->setPageCount(1);

        return $this->getMysql()->getByCriteria($criteria);
    }
    
    final public function setLimit($limit){
        $this->limit    = $limit;
        return $this;
    }
    
    final public function getLimit(){
        return $this->limit;
    }

    /**
     * Получить список по дате
     *
     * @param string $date 'YYYY-MM-DD'
     * @return MysqlList
     */
    public function listByDate($date){
        $data           = $this->getData();
        
        $criteria       = $this->getCriteria();
        $criteria->setData($data);
        $criteria->setDate( $date );
        $criteria->setStatus( ObjectComponent::STATUS_APPROVE );
        $criteria->setPageCount($this->limit);
        
        return $this->getMysql()->listByCriteria( $criteria );
    }
    
    /**
     * @param int $count
     * @return ObjectListComponent
     */
    public function listLast($count = 5){
        $data       = $this->getData();
        $criteria   = $this->getCriteria();
        
        $criteria->setData($data);
        $criteria->setStatus( ObjectComponent::STATUS_APPROVE );
        $criteria->setPageCount($count);
        $criteria->setOrder('date-');
        
        return $this->getMysql()->listByCriteria( $criteria );
    }

    /**
     * @return MysqlList
     */
    public function listAll($limit  = 5, $order = 'date'){
        $data       = $this->getData();
        $criteria   = $this->getCriteria();
        
        $criteria->setData($data);
        $criteria->setExpectStatus( ObjectComponent::STATUS_DELETE );
        $criteria->setPageCount($limit);
        $criteria->setOrder($order . '-');
        try{
            return $this->getMysql()->listByCriteria( $criteria );
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::EXPECT_ONE_RECORD) throw $e;
            return $data->createList();
        }
    }
    
    public function getRand(){
        $data       = $this->getData();
        $criteria   = $this->getCriteria();
        
        $criteria->setData($data);
        $criteria->setOrder('RAND()');
        $criteria->setPage(1);
        $criteria->setPageCount(1);
        
        return $this->getMysql()->getByCriteria($criteria);
    }

    /**
     * @return ObjectListComponent
     */
    public function listByStatus($status, $sort, $limit){
        $data       = $this->getData();
        $criteria   = $this->getCriteria();
        
        $criteria->setData($data);
        $criteria->setStatus( $status );
        $criteria->setPageCount( $limit );
        $criteria->setOrder("$sort-");
        
        return $this->getMysql()->listByCriteria( $criteria );
    }
    
    public function destroy(){
        if ($this->data){
            $this->data->destroy();
        }
        unset($this->data);
    }
}

?>