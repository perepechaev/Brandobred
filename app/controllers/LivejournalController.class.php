<?php

require_once 'app/model/livejournal/LivejournalTemplate.class.php';
require_once 'app/model/livejournal/Livejournal.class.php';
require_once 'lib/openid-simple/SimpleOpenId.class.php';

class LivejournalController extends Controller
{
    /**
     * @var LivejournalTemplate
     */
    private $view;
    
    private $lj_auth_url = 'http://www.livejournal.com/openid/approve.bml';
    private $lj_url      = 'http://www.livejournal.com/interface/xmlrpc';
    
    public function requestAuth(){
        
        if (!empty($_POST['openid_login'])){
            $this->loginToLivejournal( $_POST['identify'] );
        }
        else{
            $login = isset($_SESSION['user']['livejournal']['id']) ? $_SESSION['user']['livejournal']['id'] : ''; 
            $this->view->requestLogin( $login );
        }
        
        return $this;
    }
    
    public function approve(){
        $openid = new SimpleOpenID();
        
        // http://topas.firstvds.ru:8003/livejournal.com/approve/?openid.mode=cancel
        
        if (isset($_GET['openid_mode']) && ($_GET['openid_mode'] == 'cancel')){
            PageException::pageRedirect('/');
        }
        
        $openid->SetIdentity($_GET['openid_identity']);
        
        if ($openid->ValidateWithServer() == true){
            $this->loginFromLivejournal();
        }
        elseif ($openid->IsError() == true){
            $error = $openid->GetError();
            exception_log(new Exception($error['description']));
        }
        else {
            exception_log(new Exception('Invalid authorize with Livejournal'));
        }
        
        return $this;
    }
    
    public function setStatus(Brand $brand, Tag $tag, User $user){
        if (empty($_SESSION['user']['livejournal']['pwd'])){
            PageException::pageRedirect('livejournal.com');
        }
        
        $login = $_SESSION['user']['livejournal']['id'];
        $pwd   = $_SESSION['user']['livejournal']['pwd'];
        
        $mess = self::create();
        $mess->view->message($brand, $tag, $user);
        
        $livejournal = new Livejournal($login, $pwd, $this->lj_url);
        $livejournal->setTitle( sprintf('Моя ассоциация к „%s”', html($brand->title)) );
        $livejournal->setMessage( $mess );
        $livejournal->send();
    }
    
    private function loginFromLivejournal(){
        
        preg_match('/^http:\/\/([\w\d_]+)\./i', $_GET['openid_identity'], $match);
        $username = $match[1];
        
        $this->createUserIsNotExists($_GET['openid_identity'], $_GET['openid_identity'], ' ', $username);
        $_SESSION['user']['livejournal']['id'] = $_GET['openid_identity'];
        PageException::pageRedirect('/');
    }
    
    private function loginToLivejournal( $identify ){
        
        if (!empty($_POST['pwd'])){
            Session::need();
            $_SESSION['user']['livejournal']['pwd'] = $_POST['pwd'];
        }
        
        if (defined('DEBUG_SIMPLE_AUTHORIZE') && DEBUG_SIMPLE_AUTHORIZE == true){
            $_SESSION['user']['livejournal']['id'] = $identify;
            
            preg_match('/^http:\/\/([\w\d_]+)\./i', $identify, $match);
            $username = $match[1];
            $this->createUserIsNotExists($identify, $identify, ' ', $username);
            PageException::pageRedirect('/');
        }
        
        $openid = new SimpleOpenID();
        $openid->SetIdentity( $identify );
        $openid->SetTrustRoot( 'http://' . URL_SITE );
        $openid->SetRequiredFields( array('email','fullname', 'userid') );
        $openid->SetOptionalFields( array('dob','gender','postcode','country','language','timezone') );
        if ($openid->GetOpenIDServer()){
            $openid->SetApprovedURL( 'http://' . URL_SITE . URL_PATH . 'livejournal.com/approve/' );
            PageException::pageRedirect( str_replace('server.bml', 'approve.bml', $openid->GetRedirectURL()));
        }
        
        return $this;
    }
    
    private function createUserIsNotExists($remote_id, $alias, $email, $screen_name){
        $remote_user = UserOauthMap::instance()->getByRemoteId($remote_id, 'livejournal.com');
        
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
        $remote_user->type      = 'livejournal.com';
        $remote_user->remote_id = $remote_id;
        $remote_user->save();
        
        return $remote_user;
    }
    
    /**
     * @return LivejournalController
     */
    static public function create(){
        $self = new self();
        $self->view  = new Layout(new LivejournalTemplate(), $self);
        return $self;
    }
}

?>