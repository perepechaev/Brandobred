<?php

class UserControllerComponent extends Controller
{
    
    public $backUrl      = '';
    
    
    /**
     * @var UserListCompnent
     */
    protected $userList;
    
    public $formName     = 'login';
    public $formPass     = 'pwd';
    public $valueName    = '';
    public $valuePass    = '';
    
    protected $formErr   = '';
    protected $formTitle = 'Авторизация';
    
    /**
     * Блок отображения формы для ввода пароля и
     * имени пользователя
     *
     * @return UserController
     */
    public function loginForm(){
        $post   = SiteSkeleton::instance()->getPost();
        $get    = SiteSkeleton::instance()->getGet();        
        
        if (UserAuthorize::instance()->isLogged()){
            $this->addBlock('logoutForm', array_merge($post, $get));
        }
        else {
            $this->addBlock('loginForm', array_merge($post, $get));
        }
        return $this;
    }

    /**
     * @return UserComponent
     */
    public function authorize(){
        assert(is_object($this->userList));
        assert($this->userList instanceof UserListComponent);
        
        $contr              = $this->getController();
        $user               = UserAuthorize::instance();
        $user->setUserList( $list );

        $contr->valueName   = isset($_POST[$contr->formName]) ? $_POST[$contr->formName] : '';
        $contr->valuePass   = isset($_POST[$contr->formPass]) ? $_POST[$contr->formPass] : '';

        if ($user->isLogged()){
            $user->logout();
            $url    = SiteSkeleton::instance()->getPage()->getUriBase();
            PageException::pageRedirect( URL_SITE . '/' . $url );
        }
        elseif (empty($_POST[$contr->formName]) && empty($_POST[$contr->formPass])){
            $contr->setFormError('Надо бы все заполнить');
            $contr->backUrl = !empty($_POST['back']) ? $_POST['back'] : '';
            $result = $this->getTemplate()->getAction('loginForm');
        }
        elseif ($user->authorize($_POST[$contr->formName], $_POST[$contr->formPass])){
            $url    = !empty($_POST['back']) ? urldecode($_POST['back']) : '';
            $url    = !empty($_GET['back'])  ? urldecode($_GET['back'])  : $url;
            $url    = '/' . ltrim($url, '/');
            PageException::pageRedirect( URL_SITE . $url);
        }
        else {
            $contr->setFormError('Забыли Секретные данные?');
            $contr->backUrl = !empty($_POST['back']) ? $_POST['back'] : '';
            $result = $this->getTemplate()->getAction('loginForm');
        }
        return $result;
    }

    /**
     * Блок выполняющий выход пользователя
     *
     * Ничего не возвращает, выполняет перенаправление на
     * главную страницу
     *
     * @return UserComponent
     */
    public function logout(){
        UserAuthorize::instance()->logout();
        PageException::pageRedirect( URL_PATH );
    }

    protected function createUser($nick, $pwd, $name = false, $role = null){
        $user           = $this->getComponent()->getData();
        $user->login    = $nick;
        $user->name     = !$name ? $user->nick : $name;
        $user->password = $pwd;
        $user->register = date('Y-m-d');
        $user->lastVisit= date('Y-m-d H:i:s');
        $user->role_id  = $role;
        $user->save();
        
        return $user;
    }
    
    protected function createGuest($login, $pwd, $name = false){
        $user           = $this->getComponent()->getData();
        $user->login    = $login;
        $user->password = $pwd;
        
        $user->guest_login = $login;
        $user->guest_pwd   = $pwd;
        
        $user->name     = $name;
        $user->register = date('Y-m-d');
        $user->lastVisit= date('Y-m-d H:i:s');
        $user->role_id  = UserComponent::USER_ROLE_GUEST;
        $user->save();
        
        return $user;
    }
    

    /**
     * @return UserControllerComponent
     */
    static public function create(){
        $controller = new UserControllerComponent();
        $componet   = new UserComponent();
        $componet->setController($controller);
        $controller->setComponent($componet);
        return $controller;
    }
}

?>