<?php

require_once(dirname(__FILE__) . '/../TestHead.php');
require_once(PATH_MODEL . '/user/UserAuthorize.class.php');
require_once(PATH_MODEL . '/user/UserComponent.class.php');

if (!class_exists('UserTemplate')){
    class UserTemplate extends Template{}
}

class TestUserList  extends UserListComponent 
{
    /**
     * @return TestUserList
     */
    static public function create(){
        return new TestUserList;
    }
}

class TestSecurityAuthorize extends Test
{
    private $myIp   = '192.168.0.1';
    
    /**
     * @var UserComponent
     */
    private $component;

    public function __construct(){
        SiteSkeleton::instance()->setServer(array('REMOTE_ADDR' => $this->myIp));
        $this->component    = UserComponent::create();
        
        Mysql::instance()->setTablePrefix('security');
        
        Mysql::instance()->createTable($this->component->getData());
    }

    public function test_Session(){
        $server          = SiteSkeleton::instance()->getServer();
        
        $session         = & SiteSkeleton::instance()->getSession();
        $session_distr   = $session;
        $session['user'] = 1;
        $session['ip']   = $server['REMOTE_ADDR'];
        
        equal(SiteSkeleton::instance()->getSessionValue('user') === 1);
        
        $session_ro            = SiteSkeleton::instance()->getSession();
        $session_ro['not_set'] = 1;
        
        try{
            equal(SiteSkeleton::instance()->getServerValue('not_set') !== 1);
            equal(false);
        }
        catch (SiteSkeletonException $e){
            equal($e->getCode() === SiteSkeletonException::WRONG_PARAMETERS);
        }
        
        SiteSkeleton::instance()->setSession($session_distr);
        
        $this->result('session', 'ok');
    }
    
    public function test_userAuthorizeInstance(){
        $this->testClassSingletoon(
            UserAuthorize::instance(),
            UserAuthorize::instance()
        );

        $this->result('Initialization UserAuthorize', 'ok');
    }

    public function test_userAuthorizeDestroy(){
        $user   = UserAuthorize::instance();

        $this->testClassSingletoon($user, UserAuthorize::instance());

        UserAuthorize::destroy();
        if ($this->testClassSingletoon($user, UserAuthorize::instance(), false)){
            throw new Exception("UserAuthorize не уничтожается, дальнейшие тестирование бессмыслено");
        }

        $this->testClassSingletoon(UserAuthorize::instance(), UserAuthorize::instance());


        $this->result('Destroy UserAuthorize', 'ok');
    }

    public function test_notSessionStart(){
        
        $this->detail(true);
        
        SiteSkeleton::instance()->setCookie( new TestCookie() );
        $this->testEmptySession();
        $this->testNotLogging();
        

        $user   = UserAuthorize::instance();
        $user->setUserList( $this->getUserList() );
        try {
            $user->getName();
        }
        catch (UserException $e){
            if ($e->getCode() !== UserException::USER_NOT_AUTHORIZE) throw $e;
        }

        $data    = $this->getUser(1);
        $user->authorize($data->login, $data->password);

        $this->testNotLogging(true);
        try {
            $error  = false;
            $this->testEmptySession();
            $error  = true;
        }
        catch (Exception $e){}
        assert(!$error);

        $user->logout();
        $this->testNotLogging();
        $this->testEmptySession();

        $this->result('Session engine already success', 'ok');
    }

    public function test_authorize(){
        UserAuthorize::instance()->logout();
        UserAuthorize::destroy();
        
        $user1  = $this->getUser(1);

        $user   = UserAuthorize::instance();
        $user->setUserList( $this->getUserList() );

        // Неправильные логин/пароль
        $this->testEmptySession();
        $user->authorize($user1->login, 'not_realy_pwd');
        $this->testNotLogging();
        $this->testEmptySession();

        $user->authorize($user1->login, $user1->password);
        if ('user1' !== $user->getName()){
            throw new Exception('Ошибка авторизации');
        }
        $this->testNotLogging(true);
        $this->testEmptySession(true);

        $user->logout();
        $this->testNotLogging();
        $this->testEmptySession();

        $user2    = $this->getUser(2);
        $user->authorize($user2->login, $user2->password);
        if ('user2' !== $user->getName()){
            throw new Exception('Ошибка авторизации');
        }
        $this->testNotLogging(true);
        $this->testEmptySession(true);
        $user->logout();
        $this->testNotLogging();
        $this->testEmptySession();

        $user->authorize('/\\', $user2->password);
        $this->testNotLogging();
        $user->authorize($user1->login, $user1->password);
        $this->testNotLogging(true);
        $user->authorize($user2->login, $user2->password);
        $this->testNotLogging(true);
        $user->authorize($user2->login, $user2->password);
        if ('user2' !== $user->getName()){
            throw new Exception('Ошибка авторизации');
        }
        $this->testNotLogging(true);
        $this->testEmptySession(true);

        $user->authorize($user1->login, $user1->password);
        $user->authorize('s', 's');
        $this->testNotLogging(true);
        if ('user1' !== $user->getName()){
            throw new Exception('Ошибка авторизации');
        }
        $user->logout();

        $this->result('Authorization', 'ok');
    }

    public function test_badUserName(){
        $list       = TestUserList::create();
        $list->add($this->getUser(1));
        try {
            $user   = $this->component->getData();
            $user->password = 'pass1';
            $list->add($user);
        }
        catch (UserException $e){
            if ($e->getCode() !== UserException::EMPTY_NAME_OR_PWD) throw $e;
        }

        // Длинное имя пользователя
        $user       = $this->component->getData();
        $user->login    = str_pad('user', USER_NAME_MAX_LENGTH, 'user');
        $user->password = 'pass1';
        try {
            $list->add($user);
        } catch (UserException $e){
            if ($e->getCode() !== UserException::USER_NAME_IS_LONG) throw $e;
        }

        // Длинный пароль
        $user           = $this->component->getData();
        $user->login    = 'user2';
        $user->password = str_pad('pwd', USER_PWD_MAX_LENGTH, 'pwd');
        try {
            $list->add($user);
        } catch (UserException $e){
            if ($e->getCode() !== UserException::USER_PWd_IS_LONG) throw $e;
        }

        $badChars       = mb_convert_encoding("!#$%^&*()+~<>русские буквыЁ!'\"№;?,/\|[]{}", 'cp1251', 'utf-8');
        $badChars       = str_split($badChars);
        $badChars[]     = chr(0x00);
        $list           = TestUserList::create();
        foreach ($badChars as $key => $char){
            try {
                $user   = $this->component->getData();
                $user->login    = 'user' . mb_convert_encoding($char, 'utf-8', 'cp1251') . "_$key";
                $user->password = 'pwd';
                $user->save();
                $list->add($user);
                $this->result('Test symbol '. var_export($char, true) . "(" . ord($char) . ")", 'ERROR' );
            }
            catch (UserException $e){
                if ($e->getCode() !== UserException::WRONG_USER_NAME) throw $e;
            }
        }
        $users = $this->component->getAccessor()->listAll();

        $goodChars  = "qwertyuiopasdfghjklzxcvbnm_0123456789.";
        $goodChars  = mb_convert_encoding($goodChars, 'cp1251', 'utf-8');
        $goodChars  = str_split($goodChars);
        foreach ($goodChars as $char) {
            try {
                $user   = $this->component->getData();
                $user->login    = 'user' . $char;
                $user->password = 'pwd';
                $list->add($user);
                
                $user   = $this->component->getData();
                $user->login    = 'user' . strtoupper($char);
                $user->password = 'pwd';
                $user->save();
            }
            catch (UserException $e){
                if ($e->getCode() === UserException::WRONG_USER_NAME){
                    $this->result('Symbol '.var_export($char, true).  "(" . ord($char) . ")". ' is not a not bad', 'ERROR');
                }
                throw $e;
            }
        }
        foreach ($users as $user){
            var_dump('sss');
            print_r($user->getRawValues());
        }
        
        $this->result('Bad names for user', 'ok');
    }

    
    /*
     * Тест на определение роли пользователя (Гость)
     */
    
    public function test_isGuest(){
        $this->result('is guest', 'skip');
        return false;
        
        $mysql        = Mysql::instance();
        $controller   = UserController::create();
        $component    = $controller->getComponent();
        $mysql->dropTable($this->component->getData());
        
        $site                   = SiteSkeleton::instance();
        $cookie                 = new TestCookie();
        
        $guestLogin             = 'Тестовый_логин';
        $guestPass              = 'Тестовы_пасс';
        
        $user                   = $component->getData();
        $mysql->createTable( $user );
        
        $user->login            = 'login1';
        $user->password         = 'pass1';
        $user->name             = 'test_name';
        $user->lastVisit        = date('Y-m-d H:i:s');
        $user->register         = '1900-01-01';
        $user->date_of_birth    = '1999-01-09';
        $user->guest_login      = $guestLogin;
        $user->guest_pwd        = $guestPass;
        $user->mail             = 'mail@inbox.ru';
        $user->role_id          = UserComponent::USER_ROLE_GUEST;
        $user->save();
        
        $cookie->set('guest_pwd',$guestPass);
        $cookie->set('guest_login',$guestLogin);
        $site->setCookie($cookie);
        
        $controller->guestAuthorize();
        
        $user                   = $this->component->getAccessor()->getById($user->id);
        
        // Создадим еще одного пользователя
        $user2                  = $component->getData();
        $user2->id              = null;
        $user2->login           = 'nextLogin';
        $user2->password        = 'nextPassword';
        $user2->mail            = 'nextMail@list.ru';
        $user2->guest_login     = "2$guestLogin";
        $user2->guest_pwd       = "2$guestPass";
        $user2->save();
        
        $post['back_url'] = 'fuck';


        equal(UserAuthorize::instance()->isGuest() === true, "Метод isGuest() не сработал");
        equal(UserAuthorize::instance()->isUser() === false, "Метод isGuest() не сработал");
        equal(UserAuthorize::instance()->isModerator() === false, "Метод isGuest() не сработал");
        equal(UserAuthorize::instance()->isAdmin() === false, "Метод isGuest() не сработал");
        equal(UserAuthorize::instance()->isOwner($user) === false, "Метод isOwner не сработал: " . var_export($user->structure('getValues'), true) . var_export(UserAuthorize::instance()->getUser()->structure('getValues'), true));
        // Пробуем получить доступ к $user2 из $user
        equal(UserAuthorize::instance()->isOwner($user2) === false, "Метод isOwner не сработал");
        
        
        // Проводим регистрацию как юзера
        
        $post['login']          = 'testlogin';
        $post['password']       = 'testpass';
        $post['name']           = 'testname';
        $post['lastName']       = 'testlastName';
        $post['patronymic']     = 'testpatronymic';
        $post['birthday']       = '1900-09-09';
        $post['address']        = 'testtomsk';
        $post['telephone']      = '123456';
        $post['mail']           = 'mail@list.ru';
                    
        $site->setPost($post);
        
        try{
            $controller->registration();
            equal(false);
        }
        catch (PageException $e){
            if ($e->getCode() !== PageException::PAGE_REDIRECT) throw $e;
        }
        
        equal(UserAuthorize::instance()->isGuest() === false, "Метод isGuest() не сработал");
        equal(UserAuthorize::instance()->isUser() === true, "Метод isUser() не сработал");
        equal(UserAuthorize::instance()->isModerator() === false, "Метод isModerator() не сработал");
        equal(UserAuthorize::instance()->isAdmin() === false, "Метод isAdmin() не сработал");
        equal($user->isBlocked() === false, "Метод isBlocked() не сработал");
        equal(UserAuthorize::instance()->isOwner($user) === true, "Метод isOwner не сработал");
        // Пробуем получить доступ к $user2 из $user
        equal(UserAuthorize::instance()->isOwner($user2) === false, "Метод isOwner не сработал");
        
        
        $user = UserAuthorize::instance()->getUser();

        // Блокируем пользователя
        UserAuthorize::destroy();
        $user->status             = ObjectComponent::STATUS_DISAPPROVE;
        $user->save();

        $post['back_url'] = 'fuck';
        try {
            $controller->authorize();
            equal(false);
        }
        catch (UserControllerException $e){
            equal($e->getCode() == UserControllerException::USER_IS_BLOCKED, 'Авторизовался блокированый пользователь');
        }
        
        // Удаляем пользователя
        UserAuthorize::destroy();
        $user->status             = ObjectComponent::STATUS_DELETE;
        $user->save();

        $post['back_url'] = 'fuck';
        try {
            $controller->authorize();
            equal(false);
        }
        catch (UserControllerException $e){
            equal($e->getCode() == UserControllerException::USER_IS_BLOCKED, 'Авторизовался блокированый пользователь');
        }
        
        
        
        // Изменяем статус на Модератор, разблокируем пользователя
        
        UserAuthorize::destroy();
        $user->role_id            = UserComponent::USER_ROLE_MODERATOR;
        $user->status             = ObjectComponent::STATUS_APPROVE;
        $user->save();

        $post['back_url'] = 'http://example.com/redirect.html';
        try {
            // Нормальная авторизация, перенаправление на back_url
            $controller->authorize();
        }
        catch (PageException $e){
            if ($e->getCode() !== PageException::PAGE_REDIRECT) throw $e;
        }
        
        equal(UserAuthorize::instance()->isModerator() === true , "Метод isModerator() не сработал");
        equal(UserAuthorize::instance()->isGuest() === false, "Метод isModerator() не сработал");
        equal(UserAuthorize::instance()->isUser() === true, "Метод isModerator() не сработал");
        equal(UserAuthorize::instance()->isAdmin() === false, "Метод isModerator() не сработал");
        equal(UserAuthorize::instance()->isBlocked($user) === false, "Метод isBlocked() не сработал");
        equal(UserAuthorize::instance()->isOwner($user) === true, "Метод isOwner не сработал");
        equal(UserAuthorize::instance()->isOwner($user2) === true, "Метод isOwner не сработал");
        
        UserAuthorize::destroy();
        $user->role_id          = UserComponent::USER_ROLE_ADMIN;
        $user->save();

        $post['back_url'] = 'http://example.com/redirect.html';
        try {
            // Нормальная авторизация, перенаправление на back_url
            $controller->authorize();
        }
        catch (PageException $e){
            if ($e->getCode() !== PageException::PAGE_REDIRECT) throw $e;
        }
                
        equal(UserAuthorize::instance()->isModerator() === true , "Метод isAdmin() не сработал");
        equal(UserAuthorize::instance()->isGuest() === false, "Метод isAdmin() не сработал");
        equal(UserAuthorize::instance()->isUser() === true, "Метод isAdmin() не сработал");
        equal(UserAuthorize::instance()->isAdmin() === true, "Метод isAdmin() не сработал");
        equal(UserAuthorize::instance()->isBlocked($user) === false, "Метод isBlocked() не сработал");
        equal(UserAuthorize::instance()->isOwner($user) === true, "Метод isOwner не сработал");
        equal(UserAuthorize::instance()->isOwner($user2) === true, "Метод isOwner не сработал");
    }
    
    /*
     * Тест на определение роли пользователя (Юзер)
     */
    
    public function test_isUser(){
        
    }
    
    /*
     * Тест на определение роли пользователя (Модератор)
     */
    
    public function test_isModerator(){
        
    }
    
    /*
     * Тест на определение роли пользователя (Админ)
     */
    
    public function test_isAdmin(){
        
    }
    
    private function testClassSingletoon($obj1, $obj2, $m = 'Exception'){
        if ($obj1 !== $obj2){
            if ($m === 'Exception'){
                throw new Exception("Класс ". get_class($obj1) ." уже не Singletoon?");
            }
        }
        return $obj1 === $obj2;
    }

    private function testEmptySession($invers = false){
        if (session_id() && !$invers){
            throw new Exception('Откуда сессия?');
        }
        elseif (!session_id() && $invers) {
        	throw new Exception('Ожидается, что сессия создана');
        }
    }

    private function testNotLogging($invers = false){
        if (!UserAuthorize::instance()->isLogged() && $invers){
            throw new Exception('Пользователь незалогинен');
        }

        if (UserAuthorize::instance()->isLogged() && !$invers) {
            throw new Exception('Пользователь должен быть еще незалогинен');
        }
    }

    /**
     * @return UserList
     */
    private function getUserList(){
        $list   = TestUserList::create();
        
        $user1  = $this->component->getData();
        $user1->login    = 'user1';
        $user1->password = 'pass1';
        
        $user2  = $this->component->getData();
        $user2->login    = 'user2';
        $user2->password = 'pass2';
        
        $user3  = $this->component->getData();
        $user3->login    = 'user3';
        $user3->password = 'pass3';
        
        $list->add( $user1 );
        $list->add( $user2 );
        $list->add( $user3 );
        return $list;
    }
    
    private function getUser($number){
        $user            = $this->component->getData();
        $user->login     = "user$number";
        $user->password  = "pass$number";
        return $user; 
    }
    
    public function __destruct(){
    //    Mysql::instance()->dropTable($this->component->getData());        
    }
}

$test   = new TestSecurityAuthorize();
$test->complete();

?>