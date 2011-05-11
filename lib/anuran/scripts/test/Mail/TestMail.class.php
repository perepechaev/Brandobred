<?php

require_once(dirname(__FILE__) . '/../TestHead.php');
require_once(dirname(__FILE__). '/TestMailUtils.php');
require_once(PATH_MODEL . '/user/UserComponent.class.php');

if (!class_exists('UserTemplate')){
    class UserTemplate extends Template{}
}

class TestMail extends Test
{
    /**
     * @var TestMailUtils
     */
    private $mail   = 0;
    
    /**
     * @var UserDataComponent
     */
    private $user   = 0;

    public function test___construct(){
        $this->detail(true);
        
        $this->mail       = new TestMailUtils( dirname(__FILE__) . '/');
        $this->user       = UserComponent::create()->getData();
        
        $this->user->login      = 'user1';
        $this->user->password   = 'pwd';
        
//        $this->user->save();
        
        $this->result('test constructMail', 'ok');;
    }
    
    public function test_useMail(){
        
        $this->detail(true);
        $template         = 'UserRegistration.eml';
        $this->user->mail = MAIL_ADDR_NOREPLY;
//        $this->user->save();
        
        $this->mail->setTemplate($template);
        $this->mail->setActiveVars(
            array('user' => $this->user, 'from'=>'topas@topas.firstvds.ru', 'link' => 'simpleLink')
        );

        @$this->mail->send();
        
        $this->result('test useMail', 'ok');;
    }
    
    public function test_useMailController(){
        $this->result('test useMailController', 'skip');
        return false;
        
        $this->detail(true);
        $template         = 'UserRegistration.eml';
        $this->user->mail = MAIL_ADDR_NOREPLY . 1;
        $this->user->save();
        
        SiteSkeleton::instance()->setMail(new TestMailUtils( dirname(__FILE__)));
        $mailController = MailController::create();
        $mailController->sendRegisterMail($this->user);
        
        $this->result('test useMailController', 'ok');
    }
    
}

$test = new TestMail();
$test->complete();

?>