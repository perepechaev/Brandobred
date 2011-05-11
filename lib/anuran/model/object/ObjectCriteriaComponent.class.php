<?php

require_once PATH_CORE . '/MysqlCriteria.class.php';

class ObjectCriteriaComponent extends MysqlCriteria implements ICriteria
{
    private $id;
    
    private $ids;
    
    private $title;
    
    private $date;
    
    private $status = array(1);
    
    private $expectStatus;
    
    private $expectIds;
    
    private $page   = 1;
    
    private $count_in_page;
    
    /**
     * @param $id int
     * @return ObjectCriteriaComponent
     */
    public function setId($id){
        equal(is_numeric($id), var_export($id, true));
        
        $this->id = $id;
        return $this;
    }
    
    public function getId(){
        return $this->id;
    }
    
    public function setIds($ids){
        equal(is_array($ids));
        
        $this->ids  = $ids;
        return $this;
    }
    
    public function getIds(){
        return $this->ids;
    }
    
    public function setTitle($title){
        $this->title    = $title;
        return $this;
    }
    
    public function getTitle(){
        return $this->title;
    }
    
    public function setDate($date){
        $this->date     = $date;
    }
    
    public function getDate(){
        return $this->date;
    }
    
    public function setStatus($status){
        if (!is_array($status) && !is_null($status)){
            $status = array($status);
        }
        $this->status   = $status;
    }
    
    public function getStatus(){
        return $this->status;
    }
    
    public function setExpectStatus($status){
        if (!is_array($status)){
            $status = array($status);
        }
        $this->expectStatus = $status;
    }
    
    public function getExpectStatus(){
        return $this->expectStatus;
    }
    
    public function setExpectIds($ids){
        $this->expectIds = $ids;
    }
    
    public function getExpectIds(){
        return $this->expectIds;
    }
    
    public function setPage($page = 1){
        $this->page = $page;
    }
    
    public function getPage(){
        return $this->page;
    }
    
    public function setPageCount($count){
        $this->count_in_page    = $count;
    }
    
    public function getPageCount(){
        return $this->count_in_page;
    }
    
    public function setOrder(){
        $order  = func_get_args();
        call_user_func_array(array($this, 'order'), $order);
    }
    
    private function isDay($field, $value){
        return is_null($value) 
            ? $this->isEqual($field, null) 
            : $this->expAnd(
                $this->greater($field, $value),
                $this->letter($field, date('Y-m-d',strtotime("+1 DAY", strtotime($value))))
            );
    }
    
    protected function build(){
        $this->where(
            $this->isEqual('id', $this->getId()),
            $this->isEqual('title', $this->getTitle()),
            $this->isDay('date', $this->getDate()),
            $this->isIn('status', $this->getStatus()),
            $this->isIn('status', $this->getExpectStatus(), true),
            $this->isIn('id', $this->getExpectIds(), true),
            $this->isIn('id', $this->getIds())
        );
        $this->onbuild();
        
        $this->limit( $this->getPage(), $this->getPageCount() );
    }
    
    protected function onbuild(){
        
    }
    
    /**
     * @return ObjectCriteriaComponent
     */
    static public function create(){
        return new self();
    }
}

?>