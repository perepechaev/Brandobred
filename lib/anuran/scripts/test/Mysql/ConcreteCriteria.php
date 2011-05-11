<?php

require_once( PATH_MODEL. '/object/ObjectCriteriaComponent.class.php' );
class TestMysqlCriteriaConcreteEqual extends MysqlCriteria
{
    private $id;
    
    public function setId($id){
        $this->id = $id;
    }
    
    protected function build(){
        $this->where(
            $this->equal('service_id', $this->id)
        );
    }
}
class TestMysqlCriteriaById extends MysqlCriteria implements ICriteria 
{
    private $id;
    
    public function setId($id){
        $this->id = $id;
    }
    
    public function setPage($page = 1){}
    public function setPageCount($amount){}
    public function setOrder(){}
        
    protected function build(){
        $this->where(
            $this->equal('id', $this->id)
        );
    }
    
    static public function create($id){
        $criteria   = new self;
        $criteria->setId($id);
        return $criteria;
    }
}

class TestMysqlCriteriaConcreteManyEqual extends MysqlCriteria
{
    private $service_id;
    
    private $abonent_id;
    
    public function setServiceId($service_id){
        $this->service_id   = $service_id;
    }
    
    public function setAbonentId($abonent_id){
        $this->abonent_id   = $abonent_id;
    }
    
    public function build(){
        $this->where( $this->equal('service_id', $this->service_id) );
        $this->where( $this->equal('abonent_id', $this->abonent_id) );
    }
}

class TestMysqlCriteriaConcreteCondtitionAnd extends MysqlCriteria
{
    private $service_id;
    
    private $abonent_id;
    
    public function setServiceId($service_id){
        $this->service_id   = $service_id;
    }
    
    public function setAbonentId($abonent_id){
        $this->abonent_id   = $abonent_id;
    }
    
    public function getServiceId(){
        return $this->service_id;
    }
    
    public function getAbonentId(){
        return $this->abonent_id;
    }
    
    public function build(){
        $this->where(
            $this->expAnd(
                $this->equal('service_id', $this->service_id),
                $this->equal('abonent_id', $this->abonent_id)
            )
        );
    }
}

class TestMysqlCriteriaConcreteCondtitionOr extends TestMysqlCriteriaConcreteCondtitionAnd
{
    public function build(){
        $this->where(
            $this->expOr(
                $this->equal('service_id', $this->getServiceId()),
                $this->equal('abonent_id', $this->getAbonentId())
            )
        );
    }
}

class TestMysqlCriteriaConcreteCondtitionMulltiple extends TestMysqlCriteriaConcreteCondtitionAnd
{
    private $title;
    
    public function setTitle($title){
        $this->title    = $title;
    }
    
    public function getTitle(){
        return $this->title;
    }
    
    public function build(){
        $this->where(
            $this->expOr(
                $this->equal('service_id', $this->getServiceId()),
                $this->equal('abonent_id', $this->getAbonentId())
            ),
            $this->equal('title', $this->getTitle())
        );
    }
}

class TestMysqlCriteriaConcreteCondtitionMulltipleAnd extends TestMysqlCriteriaConcreteCondtitionMulltiple
{
    public function build(){
        $this->where(
            $this->equal('service_id', $this->getServiceId()),
            $this->equal('abonent_id', $this->getAbonentId()),
            $this->equal('title', $this->getTitle())
        );
    }
}

class TestMysqlCriteriaConcreteCondtitionMulltipleOneOr extends TestMysqlCriteriaConcreteCondtitionMulltiple
{
    public function build(){
        $this->where(
            $this->equal('title', $this->getTitle()),
            $this->expOr(
                $this->equal('service_id', $this->getServiceId())
            )
        );
    }
}

class TestMysqlCriteriaConcreteCondtitionIsEqual  extends TestMysqlCriteriaConcreteCondtitionMulltiple
{
    public function build(){
        $this->where(
            $this->isEqual('title', $this->getTitle()),
            $this->isEqual('service_id', $this->getServiceId()),
            $this->isEqual('abonent_id', $this->getAbonentId())
        );
    }
}

class TestMysqlCriteriaConcreteConditionIsEqualInCondition extends TestMysqlCriteriaConcreteCondtitionMulltiple
{
    public function build(){
        $this->where(
            $this->expOr(
                $this->isEqual('title', $this->getTitle()),
                $this->isEqual('service_id', $this->getServiceId()),
                $this->isEqual('abonent_id', $this->getAbonentId())
            )
        );
    }
}

class TestMysqlCriteriaConcreteEqualNegation extends TestMysqlCriteriaConcreteCondtitionMulltiple
{
    public function build(){
        $this->where(
            $this->isEqual('title', $this->getTitle(), true),
            $this->equal('service_id', $this->getServiceId(), true),
            $this->isEqual('abonent_id', $this->getAbonentId())
        );
    }
}

class TestMysqlCriteriaConcreteIn extends TestMysqlCriteriaConcreteCondtitionMulltiple
{
    public function build(){
        $this->where(
            $this->in('title', $this->getTitle()),
            $this->in('service_id', $this->getServiceId()),
            $this->in('abonent_id', $this->getAbonentId(), true)
        );
    }
}

class TestMysqlCriteriaConcreteInEmpty extends TestMysqlCriteriaConcreteCondtitionMulltiple
{
    public function build(){
        $this->where(
            $this->in('service_id', $this->getServiceId())
        );
    }
}

class TestMysqlCriteriaConcreteIsIn extends TestMysqlCriteriaConcreteCondtitionMulltiple
{
    public function build(){
        $this->where(
            $this->isIn('service_id', $this->getServiceId())
        );
    }
}

class TestMysqlCriteriaConcreteOrder extends TestMysqlCriteriaConcreteCondtitionMulltiple
{
    public function build(){
        $this->where(
            $this->isIn('service_id', $this->getServiceId())
        );
        
        $this->order('service_id+', 'abonent_id-');
    }
}

?>