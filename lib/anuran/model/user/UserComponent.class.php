<?php

require_once(PATH_MODEL . '/object/ObjectComponent.class.php');
require_once(dirname(__FILE__) . '/UserException.class.php');
require_once(dirname(__FILE__) . '/UserDataComponent.class.php');
require_once(dirname(__FILE__) . '/UserAccessorComponent.class.php');
require_once(dirname(__FILE__) . '/UserControllerComponent.class.php');
require_once(dirname(__FILE__) . '/UserListComponent.class.php');

class UserComponent extends ObjectComponent
{
    const USER_ROLE_UNDEFINED     = 0;
    const USER_ROLE_GUEST         = 1; 
    const USER_ROLE_ADMIN         = 2;
    const USER_ROLE_USER          = 3; 
    const USER_ROLE_MODERATOR     = 4;
 
    /**
     * @var UserControllerComponent
     */
    private $objController;
    
    protected  function __construct(){
        $this->setTemplate(   new UserTemplate());
        $this->setGiver(      new ObjectGiverComponent($this) );
        $this->setData(       new UserDataComponent($this) );
        
        $this->setAccessor(   new UserAccessorComponent($this) );
        $this->setList(       new UserListComponent() );
    }
    
    /**
     * Метод нужно рано или поздно убить
     */
    public function __call($name, $params){
        $controller = $this->getController();
        if (method_exists($controller, $name)){
            return call_user_func_array(array($controller, $params));
        }
        assert(false);
    }
    
    /**
     * @return AuctionUserAccessor
     */
    public function getAccessor(){
        return parent::getAccessor();
    }
    
    /**
     * @return DFAuctionUserData
     */
    public function getData(){
        return parent::getData();
    }
    
    /**
     * @see model/object/ObjectComponent#getController()
     * @return UserController
     */
    public function getController(){
        return parent::getController();
    }
    
    /**
     * @return  UserComponent
     */
    static public function create(){
        return new UserComponent();
    }
}

?>
