<?php

require_once 'app/model/user/User.class.php';
require_once 'app/model/user/UserBrandTag.class.php';
require_once 'app/model/user/UserTemplate.class.php';
require_once 'app/controllers/DefaultController.class.php';

class UserSession
{
    static $self;
    
    private $user;
    
    final private function __construct(){}
    
    public function isAuthorize(){
        return isset($_SESSION['user_id']) && $_SESSION['user_id'];        
    }
    
    public function isAdmin(){
        return $this->isAuthorize() && $this->getUser()->isAdmin();
    }
    
    public function isOauthAuthorize(){
        return isset($_SESSION['user_oauth']) && $_SESSION['user_oauth'];
    }
    
    public function havePossibleAuthhorize(){
        return !$this->isTwitterAuthorize() || !$this->isFacebookAuthorize() || !$this->isLivejournalAuthorize();  
    } 
    
    public function isTwitterAuthorize(){
        return isset($_SESSION['user']['twitter']['id']);
    }
    
    public function isFacebookAuthorize(){
        return isset($_SESSION['user']['facebook']['id']);        
    }
    
    public function isLivejournalAuthorize(){
        return isset($_SESSION['user']['livejournal']['id']);
    }
    
    public function hasLivejournalPassword(){
        return isset($_SESSION['user']['livejournal']['pwd']);
    }
    
    /**
     * @return User
     */
    public function getUser($reload = false){
        if ($this->user && !$reload){
            return $this->user;
        }
        assert($this->isAuthorize());
        $this->user = UserMap::instance()->getById($_SESSION['user_id']);
        return $this->user;
    }
    
    /**
     * @return UserSession
     */
    static public function instance(){
        if (!self::$self){
            self::$self = new self();
        }
        return self::$self;
    }
    
    static public function requestAdmin(){
        if (self::instance()->isAdmin()){
            return true;
        }
        PageException::pageForbidden();
    }
}

class UserController extends Controller
{
    
    /**
     * @return User
     */
    public function createUser($alias, $email = ' ', $name = ' '){
        $user        = new User();
        $user->alias = $alias;
        $user->email = $email;
        $user->name  = $name;
        $user->save();
        
        $this->storeUserTags($user);
        
        return $user;
    }
    
    public function blockProfile(){
        if (UserSession::instance()->isAuthorize()){
            $user = $this->getUserAuthorize();
            $user->avatar = isset($_SESSION['user']['twitter']['avatar']) ? $_SESSION['user']['twitter']['avatar'] : "/i/contimg/userpics/biguserpic.png";
            $this->addBlock('profile', $user);
        }
        else {
            $this->addHtml('Надо бы авторизоваться для просмотра профиля. Сейчас доступна авторизация от twitter.com');
        }
        
        return $this;
    }
    
    public function isAuthorize(){
        return UserSession::instance()->isAuthorize();
    }
    
    public function authorize($user_id, $oauth = false){
        Session::need();
        
        $user = UserMap::instance()->getById($user_id);
        
        if (!empty($_SESSION['brandtags'])){
            $this->storeUserTagsUnsaved($user, $_SESSION['brandtags']);
        }
        
        $_SESSION['user_id']    = $user->id;
        $_SESSION['user_ip']    = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_oauth'] = $oauth;
    }
    
    public function isAdmin(){
        return UserSession::instance()->isAdmin();
    }
    
    public function logout(){
        $_SESSION['user_id'] = null;
        $_SESSION['user_ip'] = null;
        session_destroy();
        PageException::pageRedirect(URL_PATH);
    }
    
    /**
     * Получить авторизованного пользователя
     * 
     * @return User
     */
    public function getUserAuthorize(){
        return UserSession::instance()->getUser();
    }
    
    /**
     * @return User
     */
    public function getIsUserAuthorize(){
        return UserSession::instance()->isAuthorize() ? $this->getUserAuthorize() : null;
    }
    
    private function storeUserTagsUnsaved(User $user, $brand_tag_ids){
        $brands = BrandMap::instance()->listByUserIdIn($user->id, array_keys($brand_tag_ids));
        
        $brand_ids = array();
        $tag_ids   = array();
        foreach ($brands as $brand){
            $brand_ids[] = $brand->id;
            $tag_ids[]   = $brand_tag_ids[$brand->id];
        }
        
        if ($brand_ids){
            $user->storeBrandTags($brand_ids, $tag_ids);
        }
    }
    
    private function storeUserTags(User $user){
        if (empty($_SESSION['brandtags'])){
            return false;
        }

        $user->storeBrandTags(array_keys($_SESSION['brandtags']), array_values($_SESSION['brandtags']));
        return true;
    }
    
    public function validateAdminIp(){
        if (!$this->isAdmin()){
            return false;
        }
        
        if (empty($_SESSION['user_ip']) || $_SERVER['REMOTE_ADDR'] !== $_SESSION['user_ip']){
            $this->logout();
        }
    } 
    
    /**
     * @return UserController
     */
    static public function create(){
        $self   = new self();
        $self->setTemplate( new UserTemplate() );
        return $self;
    }
}

?>