<?php

class User extends MysqlData
{
    const USER_GUEST = -1;
    
    public function make(){
        $this->field('id',      'int',      array('auto'));
        $this->field('alias',   'string',   array('request'));
        $this->field('role',    'enum',     array('default' => 'user', 'values' => array(
            'user', 'admin'
        )));
        
        $this->field('public_on_twitter', 'int', array('request', 'default' => '0'));
        $this->field('email',   'string',   array('request', 'default' => '', 'length' => 256));
        $this->field('name',    'string',   array('request', 'default' => '', 'length' => 256));
        
        $this->name('user');
    }
    
    public function setPublicOnTwitter($is_public){
        $is_public = (int) $is_public;
        
        if ($this->public_on_twitter == $is_public){
            return false;
        }
        
        $this->public_on_twitter = (int) $is_public;
        $this->save();
    }
    
    public function getName(){
        return $this->name ? $this->name : $this->alias;
    }
    
    public function isPublicOnTwitter(){
        return $this->public_on_twitter;
    }
    
    public function storeBrandTags($brands, $tags){
        $brands = (array) $brands;
        $tags   = (array) $tags;
        
        $model = new UserBrandTag();
        $model->user_id = $this->id;
        
        $list = $model->createList();
        foreach ($brands as $key => $brand) {
            $brand_tag = clone $model;
            $brand_tag->brand_id = $brand;
            $brand_tag->tag_id = $tags[$key];
        	$list->add($brand_tag);
        }
        $list->insert();
    }
    
    public function brands(){
        assert($this->id);
        
        $status = Status::APPROVE;
        
        return UserBrandTagMap::instance()->listByUserId($this->id, $status);
    }
    
    public function isAdmin(){
        return $this->role === 'admin';
    }
}

require_once PATH_MODEL . '/object/ObjectCriteriaComponent.class.php';
class UserCriteria extends ObjectCriteriaComponent
{
    public function getStatus(){
        return null;
    }    
}

require_once PATH_MODEL . '/object/ObjectAccessorComponent.class.php';
class UserMap extends ObjectAccessorComponent
{
    /* (non-PHPdoc)
     * @see lib/anuran/model/object/ObjectAccessorComponent#getData()
     * @return User
     */
    public function getData(){
        return new User();
    }
    
    public function getCriteria(){
        return new UserCriteria();
    }
    
    /**
     * @return UserMap
     */
    static public function instance(){
        return new self();
    }
}

?>