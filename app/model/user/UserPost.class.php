<?php

require_once 'app/model/user/PostTag.class.php';

class UserPost extends MysqlData
{
    
    protected function make(){
        $this->field('id',          'int',  array('request', 'auto'));
        $this->field('user_id',     'int',  array('request', 'default' => User::USER_GUEST));
        $this->field('comment',     'text', array('request'));
        $this->field('create',      'datetime', array('request'));
        
        $this->field('status',      'enum',     array(
            'request', 
            'default' => Status::DIRTY, 
            'values'=> Status::getAvailable()
        ));
        
        $this->name('user_post');
    }
    
    public function oncreate(){
        if ( is_null($this->create)){
            $this->create = date('Y-m-d H:i:s');
        }
        parent::oncreate();
    }
    
}


?>