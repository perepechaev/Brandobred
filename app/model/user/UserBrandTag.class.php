<?php

require_once 'app/model/brand/Brand.class.php';

class UserBrandTag extends MysqlData implements IBrand
{
    protected function make(){
        $this->field('user_id',     'int',  array('request'));
        $this->field('brand_id',    'int',  array('request'));
        $this->field('tag_id',      'int',  array('request'));
        
        $this->index('user_id');
        
        $this->name('user_brand_tag');
    }
    
    public function expectOneRecord($count){
        if ($count === 0){
            return false;
        }
        return parent::expectOneRecord($count);
    }
}

class UserBrandTagCriteria extends ObjectCriteriaComponent
{
    private $user_id;
    private $except_brand_ids;
    
    public function setUserId($user_id){
        $this->user_id = $user_id;
    }
    
    public function setExceptBrandIds($brand_ids){
        $this->except_brand_ids = $brand_ids;
    }
    
    protected function build(){
        $this->where(
            $this->equal('user_id', $this->user_id),
            $this->isIn('brand_id', $this->except_brand_ids, true)
        );
    }
    
    static public function create(){
        return new self();
    }
}

class UserBrandTagMap
{ 
    public function getData(){
        return new UserBrandTag();
    }
    
    /**
     * @return UserBrandTagCriteria
     */
    public function getCriteria(){
        return new UserBrandTagCriteria();
    }
    
    /**
     * @param $user_id  int Id - пользователя
     * @param $status   int Статус бренда
     * @return MysqlList
     */
    public function listByUserId($user_id, $status = Status::APPROVE){
        $condition = new MysqlCondition(new Brand());
        
        $criteria = new UserBrandTagCriteria();
        $criteria->setData( new UserBrandTag() )->
            join( new Brand(),  'brand_id', 'id', array('filename', 'id', 'status', 'priority', 'title'), 
                $condition->isIn('status', (array) $status))->
            join( new Tag(),    'tag_id',   'id', array('name'));
        $criteria->setCriteriaHead('user_id', 'brand_id', 'tag_id');
        $criteria->setUserId($user_id);
        $criteria->setOrder('brand_id-');
        
        return Mysql::instance()->listByCriteria($criteria);
    }
    
    /**
     * Получить список брендов, которые пользователь еще не тегировал
     * Вторым параметром передается массив id-ов для брендов которые
     * нужно исключить из выборки
     * 
     * @param $user_id      int
     * @param $brands_ids   array
     * @return MysqlList
     */
    public function listBrandsUserExcluding($user_id, $brands_ids = array()){
        assert(false);
        $criteria = $this->getCriteria();
        $criteria->setExpectBrandIds( $brands_ids );
        $criteria->setUserId($user_id);
        
        $criteria->setData($this->getData());
        
        return Mysql::instance()->listByCriteria($criteria);
    }
    
    /**
     * @return UserBrandTagMap
     */
    static public function instance(){
        return new self();
    }
}

?>