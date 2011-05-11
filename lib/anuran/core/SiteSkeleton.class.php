<?php

require_once(PATH_CORE . '/SiteSkeletonException.class.php');
require_once(PATH_CORE . '/Page.class.php');
require_once(PATH_CORE . '/Router.class.php');
require_once(PATH_CORE . '/Cookie.class.php');
require_once(PATH_MODEL . '/user/UserAuthorize.class.php');


class SiteSkeleton
{
    const PUBLIC_FILE    = -1;

    /**
     * Каталоги предназначенные для публичного доступа
     *
     * @var array
     */
    private $public     = array();

    /**
     * Контроллер страницы
     * Объект класса наследуевамого от Page
     *
     * @var Page
     */
    private $page       = null;

    /**
     * Готовый HTML
     * Метод $this->execute наполняет эту переменную,
     * метод $this->draw() выводит на экран
     *
     * @var string
     */
    private $html       = null;

    /**
     * Серверная информация
     *
     * Предпочительней чем $_SERVER
     *
     * @var array
     */
    private $server     = array();

    /**
     * $_SESSION
     *
     * @var array
     */
    private $session    = array();

    /**
     * $_GET
     *
     * @var array
     */
    private $get        = array();

    /**
     * $_POST
     *
     * @var array
     */
    private $post       = array();
    
    /**
     * @var Cookie
     */
    private $cookie;

    private $siteUrl    = null;

    /**
     * Uri сайта
     *
     * @var string
     */
    private $siteUri    = null;
    
    /**
     * @var MailUtils
     */
    private $mail       = null;

    /**
     * @var SiteSkeleton
     */
    static private $instance    = null;

    final private function __construct() {
        // Паттерн Singletoon не может быть инстанирован вручную
        // для использования объекта используется SiteSkeleton::instance()
        
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()){
            function stripslashes_deep($value){
                if(is_array($value)){
                    $value = array_map('stripslashes_deep', $value);
                }
                elseif (!empty($value) && is_string($value)){
                    $value = stripslashes($value);
                }
                return $value;
            }

            $_POST      = stripslashes_deep($_POST);
            $_GET       = stripslashes_deep($_GET);
            $_COOKIE    = stripslashes_deep($_COOKIE);
        }
    }

    /**
     * Подготовить сайт к отображению
     *
     * Загружаем структуру сайта, определяем
     * текущую страницу и действия
     * Если страница не выбрана, то выбрать ее
     * исходя из URL
     *
     * @param Router $router
     * @param string $url Текущий URL
     * @param string $siteUrl URL сайта
     */
    public function prepare($router, $url, $siteUrl) {
        $siteUrl = 'http://' . $siteUrl;
        
        // Проверяем входные параметры
        if (empty($router)) {
            SiteSkeletonException::emptyStructure();
        }

        if (!is_array($router) && !(is_object($router) && $router instanceof Router)) {
            SiteSkeletonException::expectedArray($router);
        }

        if (substr($url, 0, strlen($siteUrl)) !== $siteUrl) {
            SiteSkeletonException::urlInvalid();
        }

        // Ищем обработчик текущего урла
        $uri    = substr($url, strlen($siteUrl));
        
        // Старое использованине, оставлено для совместимости работы с массивом
        if (is_array($router)){
            $files  = array();
            foreach ($router as $filename => $value) {
                if (substr($url, 0, strlen($value)) === $value) {
                    // Приоритет имею адреса с бОльшей глубиной
                    $dep        = substr_count($value, '/');
                    if (isset($files[$dep])){
                        SiteSkeletonException::muchActions($url);
                    }
                    $files[$dep]    = $filename;
                }
            }
            krsort($files);
            $pageFile           = current($files);

            $this->preparePage($pageFile, $uri);
        }
        else {
            assert( $router instanceof Router );

            $filename    = $router->getPageName() . '.php';
            $this->preparePage($filename, $uri);
            $this->getPage()->setRouter( $router );
            $this->getPage()->setUri($uri);
            try {
                $this->getPage()->initialization();
            }
            catch (Exception $e){
                $this->getPage()->exception_initialization($e);
            }
        }
    }

    public function execute() {
        // Проверка на public-раздел
        foreach ($this->public as $public => $path){
            if (substr($this->siteUri, 0, strlen($public)) === $public){
                $filename   = $path . substr($this->siteUri, strlen($public));
                $filename   = (($pos = strpos($filename, '?')) !== false) ? substr($filename, 0, $pos) : $filename;
                $file       = StaticFile::create();

                $file->execute($filename, $this->get);

                return self::PUBLIC_FILE;
            }
        }


        $wrapper    = $this->getPage()->getWrapperName();
        if (!is_readable($wrapper)) {
            SiteSkeletonException::wrapperNotFound($wrapper);
        }
        $PAGE       =  $this->getPage();
        ob_start();
        
        $this->getPage()->prepare();
        $this->getPage()->header();
        $this->getPage()->execute();
        require_once($wrapper);
        $this->getPage()->footer();
        
        $this->html = ob_get_clean();
    }

    public function draw() {
        echo $this->html;
    }
    
    public function ajax($controller, $method){
        $filename   = PATH_CONTROLLER . '/' . $controller . '.class.php';
        FileException::isReadable($filename);
        require_once($filename);
        
        equal(class_exists($controller), 'Контроллер не найден');
        $method = (method_exists($controller, 'ajax' . $method) ? 'ajax' : 'block') . $method;
        method_exists($controller, $method) || PageException::pageNotFound();
        
        $controller = call_user_func(array($controller, 'create'));
        $controller = call_user_func(array($controller, $method));
        $controller->draw();
    }

    /**
     * @return Page
     */
    public function getPage() {
        return $this->page;
    }

    /**
     * Метод используется только в тестах
     * Выбор объекта Page возлагается полностью на метод
     * $this->prepare(...)
     *
     * @param Page $page
     */
    public function setPage(Page $page = null) {
        if (!is_null($page) || (defined('TESTING_RUN') && TESTING_RUN)){
            $this->page = $page;
        }
        else {
            SiteSkeletonException::pageIncorrect($page);
        }
    }

    /**
     * Подготовоить страницу для отображения
     *
     * Объекты класса Page подготавливаются только
     * в том случае, если мы хотим отобразить
     * страницу. Каждый инстанированный Page-объект
     * не должнен подвергаться этому методу. Этот
     * метод только для текущей страницы
     *
     * @param string $pageFile
     * @param string $uri
     */
    private function preparePage($pageFile, $uri){
        if (!$this->getPage()) {
            // Подключаем page-файл
            $pageName   = (empty($pageFile)) ? PAGE_NOT_FOUND : $pageFile;

            if (!is_readable(PATH_PAGE . '/' .$pageName)) {
                $pageName = 'index.php';
//                SiteSkeletonException::fileNotFound($pageName);
            }

            require_once(PATH_PAGE . '/' . $pageName);

            // Загружаем page-объект
            $cName      = 'Page' . substr($pageName, 0, -4);

            if (!class_exists($cName)) {
                SiteSkeletonException::classNotFound($cName, $pageName);
            }

            $page               = call_user_func(array($cName, 'instance'), $cName, $uri);
            $this->setPage($page);

            if ( !is_subclass_of($this->getPage(), 'Page') ) {
                SiteSkeletonException::pageIncorrect($this->getPage());
            }
        }
        else {
            $this->getPage()->setUri($uri);
        }
    }
    
//    public function setPage(Page $page){
//        equal(is_null($this->page), 'Page уже установлен');
//        $this->page = $page;
//    }

    public function setServer($server){
        equal(is_array($server), "Server not array: ".var_export($server, true));
        $this->server   = $server;
    }

    public function &getServer(){
        return $this->server;
    }

    public function &getServerValue($name){
        if (!isset($this->server[$name])){
            SiteSkeletonException::wrongParam('SiteSkeletone->server['.$name.']');
        }
        return $this->server[$name];
    }

    public function setSession(& $session){
        equal(is_array($session), "Session not array: ".var_export($session, true));
        $this->session  = &$session;
    }

    public function &getSession(){
        return $this->session;
    }

    public function &getSessionValue($name){
        if (!isset($this->session[$name])){
            SiteSkeletonException::wrongParam('SiteSkeletone->session['.$name.']');
        }
        return $this->session[$name];
    }
    
    public function setCookie( Cookie  $cookie ){
        $this->cookie = $cookie;
    }

    /**
     * @return Cookie
     */
    public function getCookie(){
        if (empty($this->cookie)) SiteSkeletonException::cookieDoesNotInstance();
        return $this->cookie;
    }
    
    public function setCookieValue($name, $value, $date = null){
        setcookie($name, $value, $date, URL_PATH, URL_SITE);
        $this->cookie[$name] = $value;
    }

    public function setGet(& $get){
        equal(is_array($get), "Get is not array: " . var_export($get, true));
        $this->get      = & $get;
    }

    public function &getGet(){
        return $this->get;
    }

    public function setPost(& $post){
        $this->post     = & $post;
    }

    public function &getPost(){
        return $this->post;
    }

    public function destroySession(){
        $this->session          = null;
    }

    /**
     * @param $name
     * @param $value
     * @return unknown_type
     */
    public function setSessionValue($name, $value){
        /* @var SiteSkeleton */
        $this->session[$name]   = $value;
    }

    public function startSession(){
        if (!session_id()){
            session_start();
            $this->getCookie()->set(session_name(), session_id());
        }
        
        if (!$this->session){
            $this->session = &$_SESSION;
        }
        else{
            equal(false, 'Попытка стартануть сессию провалена, т.к. в сессии есть какая-то ....');
        }
    }

    public function setUri($uri){
        $this->siteUri  = $uri;
    }

    public function setPublic($public){
        $this->public   = $public;
    }
    
    /**
     * @param MailUtils $mail
     */
    public function setMail($mail){
        $this->mail = $mail;
    }
    
    /**
     * @return MailUtils
     */
    public function getMail(){
        equal(isset($this->mail));
        return $this->mail;
    }
    
    
    /**
     * @return SiteSkeleton
     */
    final static public function instance() {
        if (is_null(SiteSkeleton::$instance)) {
            SiteSkeleton::$instance = new SiteSkeleton();
        }
        return SiteSkeleton::$instance;
    }
}

?>