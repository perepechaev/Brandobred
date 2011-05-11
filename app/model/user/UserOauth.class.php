<?php

require_once PATH_MODEL . '/object/ObjectComponent.class.php';

class UserOauth extends MysqlData
{
    public function make(){
        
        $this->field('user_id',     'int',  array('request'));
        
        $this->field('type',        'enum', array('request', 'values' => array(
            'twitter.com', 'facebook.com', 'livejournal.com'
        )));
        $this->field('remote_id',   'string',  array('request'));
        
        $this->index('user_id');
        $this->index(array('type', 'remote_id'));
        
        $this->name('user_oauth');
    }
    
    public function expectOneRecord($count){
        if ($count === 0){
            return false;
        }
        return parent::expectOneRecord($count);
    }
}

class UserOauthMap extends ObjectAccessorComponent
{
    public function getData(){
        return new UserOauth();
    }
    
    public function getByType($type, $remote_id){
        
    }
    
    public function getByUserId($user_id, $type){
        $criteria = new UserOauthCriteria();
        $criteria->setUserId($user_id);
        
        return $this->getByCriteria($criteria);
    }
    
    public function getByRemoteId($remote_id, $type){
        $criteria = new UserOauthCriteria();
        $criteria->setRemoteId($remote_id);
        $criteria->setType($type);
        
        return $this->getByCriteria($criteria);
    }
    
    /**
     * @return UserOauthMap
     */
    static public function instance(){
        return new self();
    }
}

class UserOauthCriteria extends ObjectCriteriaComponent
{
    private $user_id;
    private $remote_id;
    
    private $type;
    
    public function setUserId($user_id){
        $this->user_id = $user_id;
    }
    
    public function setRemoteId($remote_id){
        $this->remote_id = $remote_id;
    }
    
    public function setType($type){
        $this->type = $type;
    }
    
    protected function onbuild(){
        $this->where(
            $this->isEqual('user_id',   $this->user_id),
            $this->isEqual('remote_id', $this->remote_id),
            $this->isEqual('type',      $this->type)
        );
    }
    
}

?>