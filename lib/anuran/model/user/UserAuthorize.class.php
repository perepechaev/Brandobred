<?php

require_once(PATH_CORE .'/SiteSkeleton.class.php');
require_once(PATH_MODEL .'/user/UserException.class.php');
require_once(PATH_MODEL .'/user/UserListComponent.class.php');

class UserAuthorize
{
    static private $instance;

    protected $name     = 'authorize';
    
    private $session;

    /**
     * @var UserList
     */
    protected $userList;
    
    final private function __construct(){
        if (isset($_REQUEST[session_name()])){
            SiteSkeleton::instance()->startSession();
        }

        $server     = SiteSkeleton::instance()->getServer();
        $session    = $this->getSession();
        if (isset($session['user']) && ($session['ip'] !== $server['REMOTE_ADDR'])){
            UserException::substitutionSession(var_export($session['ip'], true), $server['REMOTE_ADDR']);
        }
    }

    public function isLogged(){
        $session    = $this->getSession();
        return !empty($session['user']);
    }

    
    public function isGuest(){
        return $this->isLogged() && $this->getUser()->isGuest();
    }
    
    public function isUser(){
        return $this->isLogged() && ($this->isModerator() || $this->getUser()->isUser());
    }
        
    public function isModerator(){
        return $this->isLogged() && ($this->isAdmin() || $this->getUser()->isModerator());
    }
    
    public function isAdmin(){
        return $this->isLogged() && $this->getUser()->isAdmin();
    }
    
    public function isOwner(UserDataComponent $user){
        return $this->isUser() && ($this->isAdmin() || (($user->id == $this->getUser()->id || $this->isModerator()) && !$user->isAdmin())); 
    }
    
    public function isBlocked(UserDataComponent $user){
        return $user->isBlocked();
    }
    
    public function getName(){
        $session    = $this->getSession();
        if (empty($session['user'])){
            UserException::userNotAuthorize();
        }
        return $session['user'];
    }

    /**
     * @return UserDataComponent
     */
    public function getUser(){
        equal($this->isLogged(), 'Попытка получения неавторизованного пользователя');
        $session    = $this->getSession();
        return $session['userObject'];
    }

    public function setUserList(UserListComponent $list){
        $this->userList = $list;
    }

    public function authorize($user, $pass) {
        if (!($this->userList instanceof UserListComponent)){
            UserException::userListNotDefined();
        }
        $userData   = $this->userList->find($user, $pass);
        if ($userData) {
            SiteSkeleton::instance()->destroySession();
            SiteSkeleton::instance()->startSession();
            $userData->setIp(SiteSkeleton::instance()->getServerValue('REMOTE_ADDR'));
            $this->getSession();
            $this->session = array(
                'user'      => $user,
                'ip'        => $userData->getIp(),
                'isAdmin'   => $this->isAdmin(),
                'userObject'=> $userData
            );
//            dump($this->session, false, 'blue');
        }
        
        return $userData;
    }

    static public function logout() {
        if (session_id()){
            SiteSkeleton::instance()->getCookie()->delete(session_name());
            session_destroy();
            SiteSkeleton::instance()->destroySession();
        }
    }

    /**
     * @return UserAuthorize
     */
    static public function instance(){
        if (!isset(self::$instance)){
            self::$instance = new UserAuthorize();
        }
        return self::$instance;
    }

    private function getSession(){
        $session    = & SiteSkeleton::instance()->getSession();
        if (empty($session[$this->name])){
            $session[$this->name]    = null;
        }
        $this->session    = & $session[$this->name];
        
        return $this->session;
    }
    
    private function destroyAuthorizeSession(){
        $session    = & SiteSkeleton::instance()->getSession();
        unset($session[$this->name]);
        
        return $this->session;
    }
    
    /**
     * Уничтожить Singletoon! объект.
     *
     * Необходим ИСКЛЮЧИТЕЛЬНО в тестах
     * Во всех остальных местах использование
     * КРАЙНЕ нежелательно
     *
     */
    static public function destroy(){
        if (!defined('TESTING_RUN') || (TESTING_RUN !== true)){
            throw new Exception("Попытка уничтожить класс UsreAuthorize! Безобразие.");
        }
        self::instance()->session = null;
        self::$instance = null;
    }
}


?>