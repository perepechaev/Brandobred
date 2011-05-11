<?php

require_once 'app/model/user/User.class.php';
require_once 'app/model/user/UserOauth.class.php';
require_once 'app/controllers/UserController.class.php';
require_once 'lib/facebook/facebook.php';
require_once 'lib/facebook/facebook_desktop.php';
require_once 'lib/facebook/jsonwrapper/JSON/JSON.php';

class FacebookController extends Controller
{
    /**
     * @var TwitterTemplate
     */
    private $view;
    
    private $user;
    
    /**
     * @var Facebook
     */
    private $facebook;
    
    public function requestAuth(){
        $this->facebook = new Facebook(FACEBOOK_KEY, FACEBOOK_SECRET);
        $this->addHtml('<h3>Facebook авторизация</h3>');
        
        Session::need();
        
        if (isset($_GET['session'])){
            $session = json_decode($_GET['session']);
            // fix float value to string
            preg_match('/"uid":(\d+)\,/', $_GET['session'], $uid);
            $session->uid = $uid[1];
            $this->facebook->set_user($uid[1], $session->session_key, $session->expires + 10000, $session->secret);
            $this->facebook->api_client->set_user($uid[1], $this->facebook->api_client->session_key);
            $_SESSION['facebook']['session'] = $session;
        }
        
        if (!($this->user = (string) $this->facebook->get_loggedin_user())){
            PageException::pageRedirect(sprintf('http://www.facebook.com/login.php?api_key=%s&connect_display=page&v=1.0&next=%s&cancel_url=http://www.facebook.com/connect/login_failure.html&fbconnect=true&return_session=true&session_key_only=true&req_perms=read_stream,publish_stream', 
                FACEBOOK_KEY,
                'http://' . URL_SITE . '/facebook.com/'
            ));
        }
        $this->facebook = new Facebook(FACEBOOK_KEY, $_COOKIE[FACEBOOK_KEY . '_ss']);
        $this->facebook->set_user($this->user, $_COOKIE[FACEBOOK_KEY . '_session_key'], 0, $_COOKIE[FACEBOOK_KEY . '_ss']);
        $info = $this->facebook->api_client->users_getLoggedInUser();
        
        PageException::pageRedirect('/facebook.com/confirmation/');
    }
    
    public function blockConfirmation(){
        $this->cookie2session();
        
        $session = $_SESSION['facebook']['session'];
        
        $this->facebook = new Facebook(FACEBOOK_KEY, $session->secret);
        $this->facebook->set_user($session->uid, $session->session_key, 0, $session->secret);
        $info = $this->facebook->api_client->users_getInfo($session->uid, 'name,first_name,email');
        
        $this->createUserIsNotExists($info[0]['uid'], $info[0]['first_name'], $info[0]['email'], $info[0]['name']);
        $_SESSION['user']['facebook']['id'] = $info[0]['uid'];
        PageException::pageRedirect('/');
    }
    
    public function setStatus($status, $uid){
        $session = $_SESSION['facebook']['session'];
        
        $this->facebook = new Facebook(FACEBOOK_KEY, $session->secret);
        $this->facebook->set_user($session->uid, $session->session_key, 0, $session->secret);
        $this->facebook->api_client->users_setStatus($status, $_SESSION['user']['facebook']['id']);
        return true;
    }
    
    private function cookie2session(){
        $session = new stdClass();
        $session->secret = $_COOKIE[FACEBOOK_KEY . '_ss'];
        $session->uid    = $_COOKIE[FACEBOOK_KEY . '_user'];
        $session->session_key    = $_COOKIE[FACEBOOK_KEY . '_session_key'];
        $_SESSION['facebook']['session'] = $session;
    }
    
    private function createUserIsNotExists($remote_id, $alias, $email, $screen_name){
        $remote_user = UserOauthMap::instance()->getByRemoteId($remote_id, 'facebook.com');
        
        if (!$remote_user){
            if (empty($_SESSION['user_id'])){
                $user = UserController::create()->createUser($alias, $email, $screen_name);
                $_SESSION['user_id'] = $user->id;
            }
            
            $remote_user = $this->createRemoteUser($_SESSION['user_id'], $remote_id);
        }
        
        UserController::create()->authorize($remote_user->user_id, true);
    }
    
    private function createRemoteUser($user_id, $remote_id){
        $remote_user = new UserOauth();
        $remote_user->user_id   = $user_id;
        $remote_user->type      = 'facebook.com';
        $remote_user->remote_id = $remote_id;
        $remote_user->save();
        
        return $remote_user;
    }
    
    /**
     * @return TwitterController
     */
    static public function create(){
        
        try{
            Session::need();
            $controller = new self();
            $controller->view  = new Layout(new TwitterTemplate(), $controller);
        }
        catch (Exception $e){
            exception_log($e);
        }
        
        return $controller;
    }
}

?>
