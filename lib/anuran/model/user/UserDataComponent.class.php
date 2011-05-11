<?php

require_once(PATH_MODEL . '/object/ObjectDataComponent.class.php');

class UserDataComponent extends ObjectDataComponent
{
    private $ip;
    
    public function make(){
        $this->field('id',          'int',      array('request', 'unsigned', 'auto'));
        $this->field('login',       'string',   array('unique', 'request', 'length'=>24));
        $this->field('password',    'string',   array('request', 'length'=>32));
        $this->field('mail',        'string',   array('request', 'length'=>128, 'default'=>" "));
        $this->field('name',        'string',   array('length'=>32)); // Имя пользователя
        $this->field('lastVisit',   'datetime', array('request'));
        $this->field('register',    'date',     array('request'));
        $this->field('role_id',     'int');
        $this->field('status',      'int',      array('default' => ObjectComponent::STATUS_UNPROVEN));
        
        $this->name('user');
        equal(isset($this->component), 'Не выбрана компонента');
    }
    
    public function setIp($ip){
        $this->ip = $ip;
    }
    
    public function getIp(){
        equal(!empty($this->ip), "Не установлен ip пользователя");
        return $this->ip;
    }
    
    public function setPassword($password){
        return md5($password . USER_PWD_SALT);
    }
    
    public function setLogin($login){
        return mb_strtolower($login);
    }
    
    public function isAdmin(){
        return $this->role_id == UserComponent::USER_ROLE_ADMIN;
    }

    public function isUser(){
        return $this->role_id == UserComponent::USER_ROLE_USER;
    }
    
    public function isGuest(){
        return $this->role_id == UserComponent::USER_ROLE_GUEST;
    }
    
    public function isModerator(){
        return $this->role_id == UserComponent::USER_ROLE_MODERATOR;
    }
    
    public function isBlocked(){
        return $this->status == ObjectComponent::STATUS_DELETE || $this->status == ObjectComponent::STATUS_DISAPPROVE;
    }
    
    public function isConfirmation(){
        return $this->confirmation  == null;
    }

    public function isAuthorize(){
        return UserAuthorize::instance()->isUser() && $this->id == UserAuthorize::instance()->getUser()->id;
    }
    
    public function prepare(){
        UserException::testUserNamePwd($this->login, $this->password);
        
        if (!$this->lastVisit){
            $this->lastVisit = date('Y-m-d H:i:s');
        }
        
        if (!$this->register){
            $this->register  = date('Y-m-d'); 
        }
    }
    
}

?>