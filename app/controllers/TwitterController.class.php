<?php

require_once 'app/model/user/User.class.php';
require_once 'app/model/user/UserOauth.class.php';
require_once 'app/controllers/UserController.class.php';

require_once 'app/model/twitter/TwitterAPI.class.php';
require_once 'app/model/twitter/TwitterTemplate.class.php';

class TwitterController extends Controller
{
    const AUTH_STATE_NONE       = 0;
    const AUTH_STATE_PROCCESS   = 1;
    const AUTH_STATE_SUCCESS    = 2;
    
    /**
     * @var TwitterApi
     */
    public $api;
    
    /**
     * @var TwitterTemplate
     */
    private $view;
    
    private $requestUrl     = 'http://twitter.com/oauth/request_token';
    private $authorizeUrl   = 'http://twitter.com/oauth/authorize';
    private $accessUrl      = 'http://twitter.com/oauth/access_token';
    
    private $token;
    private $secret;
    
    private $state;
    
    /**
     * @var OAuth
     */
    private $oauth;
    
    public function requestAuth(){
        if (defined('DEBUG_SIMPLE_AUTHORIZE') && DEBUG_SIMPLE_AUTHORIZE){
            UserController::create()->authorize(1, true);
            $this->blockConfirmation();
            $_SESSION['user']['twitter']['id'] = 1;
            return $this;
        }
        
        if ($this->isDenied()){
            $this->state = $_SESSION['state'] = self::AUTH_STATE_NONE;
            $this->blockDenied();
            return $this;
        }
        
        if (isset($_SESSION['state']) && $_SESSION['state'] === self::AUTH_STATE_PROCCESS && empty($_GET['oauth_token'])){
            $_SESSION['state'] = self::AUTH_STATE_NONE;
            $this->state = self::AUTH_STATE_NONE;
        }
        
        
        if( $this->isEmptyConnection() ) {
            $this->actionGetToken();
        } 
        elseif( $this->isProccessAuthorize() ) {
            $this->actionAuthorize();
        }
        elseif( $this->isAuthorize() ){
            $this->blockConfirmation();
        }
        
        $this->oauth->setToken( $this->token, $this->secret);
        
                        
        return $this;
    }
    
    public function blockDenied(){
        $this->addHtml('<h3>Не удалось авторизоваться при помощи twitter.com</h3>');
        $this->addHtml('<div><a href="/">вернуться</a> к написанию тегов</div>');
        return $this;
    }
    
    public function blockConfirmation(){
        $user = UserSession::instance()->getUser();
        $this->view->authorizeConfirmation( $user );
        return $this;
    }
    
    public function blockDisable(){
        $user = UserController::create()->getUserAuthorize();
        $user->setPublicOnTwitter(false);
        
        PageException::pageBackRedirect();
    }
    
    public function blockEnable(){
        $user = UserController::create()->getUserAuthorize();
        $user->setPublicOnTwitter(true);
        
        PageException::pageBackRedirect();
    }
    
    private function actionGetToken(){
//    	$this->oauth->disableRedirects();
    	$info = $this->oauth->getRequestToken($this->requestUrl);
        
        $_SESSION['secret'] = $info['oauth_token_secret'];
        $_SESSION['state']  = self::AUTH_STATE_PROCCESS;
        
        PageException::pageRedirect($this->authorizeUrl.'?oauth_token='.$info['oauth_token']);
    }
    
    private function actionAuthorize(){
        $this->oauth->setToken($_GET['oauth_token'], $this->secret);
        $info   = $this->oauth->getAccessToken($this->accessUrl);
        
        $_SESSION['state']  = self::AUTH_STATE_SUCCESS;
        $_SESSION['token']  = $info['oauth_token'];
        $_SESSION['secret'] = $info['oauth_token_secret'];
        $_SESSION['user']['twitter']['id'] = $info['user_id'];
        $_SESSION['user']['twitter']['avatar'] = $this->api->getAvatarUrl($info['user_id']);

        
        $this->createUserIsNotExists($info['user_id'], $info['screen_name']);
        
        PageException::pageRedirect('/twitter.com/confirmation/');
    }
    
    private function createUserIsNotExists($remote_id, $screen_name){
        $remote_user = UserOauthMap::instance()->getByRemoteId($remote_id, 'twitter.com');
        
        if ($remote_user){
            $this->authorize($remote_user->user_id);
        }
        else {
            if (empty($_SESSION['user_id'])){
                $user = UserController::create()->createUser($screen_name, ' ', $this->api->getUserName($remote_id));
                $_SESSION['user_id'] = $user->id;
            }
            $remote_user = $this->createRemoteUser($_SESSION['user_id'], $remote_id);
        }
        
        $this->authorize($remote_user->user_id);
    }
    
    private function authorize($user_id){
        UserController::create()->authorize($user_id, true);
    }
    
    /**
     * @param $alias
     * @return User
     */
    private function createUser($alias){
        exception_log(new Exception('Использование устаревшего метода. Рекомендуется пользоваться UserController::create()->createUser($alias)'));
        return UserController::create()->createUser($alias);
    }
    
    private function createRemoteUser($user_id, $remote_id){
        $remote_user = new UserOauth();
        $remote_user->user_id   = $_SESSION['user_id'];
        $remote_user->type      = 'twitter.com';
        $remote_user->remote_id = $remote_id;
        $remote_user->save();
        
        return $remote_user;
    }
    
    public function isAuthorize(){
        if (!isset($_GET['oauth_token']) && $this->state == 1) {
            $this->state = $_SESSION['state'] = self::AUTH_STATE_NONE;
        }
        return $this->state == self::AUTH_STATE_SUCCESS;
    }
    
    public function isPublicMessage(){
        $user = UserSession::instance();
        
        if (!$user->isAuthorize()){
            return false;
        }
        
        if (!$this->isAuthorize()){
            return false;
        }
        
        return $user->getUser()->isPublicOnTwitter();        
    }
    
    public function isDenied(){
        return !empty($_GET['denied']);
    }
    
    public function isEmptyConnection(){
        return !isset($_GET['oauth_token']) && !$this->state;
    }
    
    public function isProccessAuthorize(){
        return ($this->state == self::AUTH_STATE_PROCCESS) && isset($_GET['oauth_token']);        
    }
    
    private function init(){
        $this->token    = isset($_SESSION['token'])  ? $_SESSION['token']  : null;
        $this->secret   = isset($_SESSION['secret']) ? $_SESSION['secret'] : null;
        $this->state    = isset($_SESSION['state'])  ? $_SESSION['state']  : self::AUTH_STATE_NONE;
    }
    
    /**
     * @return TwitterController
     */
    static public function create(){
        
        try{
            Session::need();
            $controller = new self();
            
            equal(extension_loaded('oauth'), 'PECL library oauth not found');
            $controller->init();
            $controller->oauth = new OAuth(TWITTER_KEY, TWITTER_SECRET, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
            
            $controller->api   = new TwitterApi($controller->oauth);
            
            $controller->view  = new Layout(new TwitterTemplate(), $controller);
        }
        catch (Exception $e){
            exception_log($e);
        }
        
        return $controller;
    }
}

?>
