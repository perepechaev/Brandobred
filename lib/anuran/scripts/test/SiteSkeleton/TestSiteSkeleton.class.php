<?php

require_once(dirname(__FILE__) . '/../TestHead.php');

require_once(PATH_CORE . '/SiteSkeleton.class.php');
require_once(dirname(__FILE__) . '/PageTest.class.php');

$site   = SiteSkeleton::instance();

class TestSiteSkeleton extends Test
{
    /**
     * @var SiteSkeleton
     */
    private $site;

    public function __construct(){
        $this->site = SiteSkeleton::instance();
    }

    public function test_emptyStructure(){
        // Пустая структура
        $structure  = array();
        $url        = URL_SITE;
        $message    = "Test empty structure";
        $result     = 'ERROR';
        try {
            $this->site->prepare($structure, $url, URL_SITE);
        }
        catch (Exception $e) {
            $result = (get_class($e) === 'SiteSkeletonException') &&
                      ($e->getCode() === SiteSkeletonException::EMPTY_STRUCTURE)
                    ? 'ok' : 'ERROR' . " (". get_class($e) .", {$e->getCode()}) in " . __FILE__ . "(". __LINE__ .")";
        }
        $this->result($message, $result);
    }

    public function test_structureExpectedArray(){
        // Пробуем передать откровенную чушь вместо массива
        $structure  = 'dust';
        $url        = URL_SITE;
        $message    = 'Test expected array';
        $result     = 'ERROR';
        try {
            $this->site->prepare($structure, $url, URL_SITE);
        }
        catch (Exception $e) {
            $result = (get_class($e) === 'SiteSkeletonException') &&
                      ($e->getCode() === SiteSkeletonException::EXPECTED_ARRAY )
                    ? 'ok' : 'ERROR' . " (". get_class($e) .", {$e->getCode()}) in " . __FILE__ . "(". __LINE__ .")";
        }
        $this->result($message, $result);
    }

    public function test_invalidUrl(){
        // Неправильный урл
        $structure  = array(
            'index.php' => URL_SITE . '/index.php'
        );
        $url        = URL_SITE . '/index.php';
        $siteUrl    = URL_SITE . '/unknowSite';
        $message    = 'Test url exception';
        $result     = 'ERROR';
        try {
            $this->site->prepare($structure, $url, $siteUrl);
        }
        catch (Exception $e) {
            $result = (get_class($e) === 'SiteSkeletonException') &&
                      ($e->getCode() === SiteSkeletonException::URL_INVALID  )
                    ? 'ok' : 'ERROR' . " (". get_class($e) .", {$e->getCode()}) in " . __FILE__ . "(". __LINE__ .")";
        }
        $this->result($message, $result);
    }

    public function test_muchActions(){
        // XXX: Делегированно в Router;
        return false;
        
        // Множественные действия
        $this->detail = true;
        $structure  = array(
            'list1.php' => URL_SITE . '/list1',
            'list2.php' => URL_SITE . '/list2',
            'list3.php' => URL_SITE . '/list1',
            'list4.php' => URL_SITE . '/list1',
        );
        $url        = 'http://' . URL_SITE . '/list1/';
        try {
            $this->site->prepare($structure, $url, URL_SITE);
            equal(false);
        }
        catch (SiteSkeletonException $e) {
            if ($e->getCode() !== SiteSkeletonException::MUCH_ACTIONS) throw $e;
        }
        $this->result('Test much actions', 'ok');
    }

    public function test_notFoundFile(){
        // XXX: Переписать с учетом использования Router;
        return false;
        
        // Не найден файл страницы
        $structure  = array(
            'list1.php' => URL_SITE . '/list1/',
            'list2.php' => URL_SITE . '/list2/',
            'list3.php' => URL_SITE . '/list3/',
            'list4.php' => URL_SITE . '/list4/',
        );
        $url        = 'http://' . URL_SITE . '/list3/';
        $message    = 'Test not found file';
        $this->site->setPage(null);
        try {
            $this->site->prepare($structure, $url, URL_SITE);
            $this->error(new Exception('Не сработал SiteSkeletoneException'), __LINE__);
        }
        catch (SiteSkeletonException $e) {
            if ($e->getCode() === SiteSkeletonException::FILE_NOT_FOUND ){
                $this->result($message, 'ok');
            }
            else {
                $this->error($e, __LINE__ );
            }
        }
    }

    public function test_prepare(){
        // Нормальная загрузка
        $message    = 'Test prepare';
        
        $structure  = array(
            '/'			=> 'index.php'
        );

        $this->site->setPage( new PageTestDefaultWrapper() );
        $this->site->prepare($structure, 'http://' . URL_SITE . '/', URL_SITE);
        $result = 'ok';

        $this->result($message, $result);
    }

    public function test_defaultWrapper(){
        // Выбор обертки
        $message    = 'Test default wrapper';
        $page       = new PageTestDefaultWrapper();

        $structure  = array(
            '/'			=> 'index.php'
        );
        
        $this->site->setPage( $page );
        $this->site->prepare($structure, 'http://' . URL_SITE . '/', URL_SITE);
        $result = ($page->getWrapperName() === PATH_TEMPLATE . '/wrapper.php')
                ? 'ok'
                : 'ERROR ('.$page->getWrapperName().") in " . __FILE__ . "(". __LINE__ .")";

        $this->result($message, $result);
    }

    public function test_selectWrapper(){
        // Доступность файла обертки
        $message    = 'Test access for default wrapper';
        $page       = new PageTestDefaultWrapper();
        $result     = is_readable($page->getWrapperName()) ? 'ok' : 'ERROR ('.$page->getWrapperName().") in " . __FILE__ . "(". __LINE__ .")";
        $this->result($message, $result);
    }

    public function test_accessWrapper(){
        // Page с уникальной оберткой
        $message    = 'Test selection wrapper';
        $page       = new PageTestSelectedWrapper();
        $result     = ($page->getWrapperName() === PATH_TEMPLATE . '/test_wrapper.php')
                    ? 'ok'
                    : 'ERROR ('.$page->getWrapperName().") in " . __FILE__ . "(". __LINE__ .")";
        $this->result($message, $result);
    }

    public function test_accessSelectedWrapper(){
        return false;
        // Доступ к уникальной обертке
        $message    = 'Test access for selection wrapper';
        $page       = new PageTestSelectedWrapper();
        $result     = is_readable($page->getWrapperName()) ? 'ok' : 'ERROR ('.$page->getWrapperName().") in " . __FILE__ . "(". __LINE__ .")";
        $this->result($message, $result);
    }

    public function test_notFoundWrapper(){
        // Обертка не найдена
        $message    = 'Test not found wrapper';
        $page       = new PageTestNotFoundWrapper();
        $result     = 'ERROR';
        try {
            $this->site->setPage($page);
            $this->site->execute();
            $result = 'ERROR';
        }
        catch (Exception $e) {
            $result = (get_class($e) === 'SiteSkeletonException') &&
                      ($e->getCode() === SiteSkeletonException::WRAPPER_NOT_FOUND)
                    ? 'ok' : 'ERROR' . " (". get_class($e) .", {$e->getCode()}) in " . __FILE__ . "(". __LINE__ .")";
        }
        $this->result($message, $result);
    }

    public function test_session(){
        $site   = SiteSkeleton::instance();

        $SESS   = array();
        $site->setSession($SESS);
        $SESS['newrecord']  = true;

        if ($new = &$site->getSessionValue('newrecord') !== true) {
            throw new Exception("Сессионный массив должен передаваться по ссылке");
        }

        $new    = false;
        if ($SESS['newrecord'] !== false){
            throw new Exception("Сессионный массив должне сохранять значения");
        }

        SiteSkeleton::instance()->destroySession();

        assert(is_null($SESS));
    }
    
    /**
     * Проверить, умеет ли SiteSkeleton работать с куками.
     *
     */
    public function test_cookie(){
        $site       = SiteSkeleton::instance();
        $this->detail(true);
        
        try{
            $site->getCookie();
            equal(0, "Попытка получения объекта Cookie без инстанирования: " . var_export($site->getCookie(), true));
        }
        catch (SiteSkeletonException $e ){
            if ($e->getCode() !== SiteSkeletonException::COOKIE_DOES_NOT_INSTANCE) throw $e;
        }
        
        try{
            $site->setCookie($this);
            equal(0, "SiteSkeleton->setCookie() принимает на вход объект класса Cookie");
        }
        catch (Exception $e){
            if ($e->getCode() !== E_RECOVERABLE_ERROR) throw $e;
        }
        
        $site->setCookie( new Cookie());
        
        $cookie = $site->getCookie();
        equal($cookie instanceof Cookie);
        equal($cookie === $site->getCookie());
        
        $this->result('Test Cookie', 'ok');
    }
    
}

$test   = new TestSiteSkeleton();
$test->complete();


?>