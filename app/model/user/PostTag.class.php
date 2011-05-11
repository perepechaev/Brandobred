<?php

class PostTag extends MysqlData
{
    protected function make(){
        $this->field('id',          'int', array('request', 'auto'));
        $this->field('post_id',     'int', array('request'));
        $this->field('tag_id',      'int', array('request'));
        $this->field('brand_id',    'int', array('request'));
        
        $this->name('post_tag');
    }
}

class PostTagMap extends BrandMap
{
    public function getData(){ 
        return new UserPost();
    }
    
    public function listPostByTagId($tag_id){
        $condition  = new MysqlCondition($this->getData());
        
        $criteria   = new BrandTagCriteria();
        
        $criteria->setData( new PostTag() );
        $criteria->join( $this->getData(), 'post_id', 'id', 
            array('user_id', 'comment', 'create', 'status'),
            $condition->in('status', array(Status::APPROVE, Status::DIRTY)) 
        );
        
        $criteria->setTagId($tag_id);
        
        return Mysql::instance()->listByCriteria($criteria);
    }
    
    public function listPostByBrandId($brand_id){
        $condition  = new MysqlCondition( new PostTag() );
        
        $criteria   = new BrandTagCriteria();
        
        $criteria->setData( new UserPost() );
        $criteria->join( new PostTag(), 'id', 'post_id', 
            array('brand_id'),
            $condition->equal('brand_id', $brand_id)
        );
        $criteria->join( new User(), 'user_id', 'id', 
            array('alias', 'name', 'email')
        );
        
        $criteria->setStatus(array(Status::APPROVE));
        
        $criteria->setOrder('create-');
        
        return Mysql::instance()->listByCriteria($criteria);
    }
    
    public function listDirty(){
        $condition  = new MysqlCondition( new PostTag() );
        
        $criteria   = new BrandTagCriteria();
        
        $criteria->setData( new UserPost() );
        $criteria->join( new User(), 'user_id', 'id', 
            array('alias', 'name', 'email')
        );
        $criteria->join( new PostTag(), 'id', 'post_id', 
            array('brand_id')
        );
        
        
        $criteria->setStatus(Status::DIRTY);
        
        $criteria->setOrder('create-');
        
        return Mysql::instance()->listByCriteria($criteria);
    }
    
    
    
    /**
     * @return PostTagMap
     */
    static public function instance(){
        return new self();
    }
}

?>