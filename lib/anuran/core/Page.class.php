<?php

abstract class Page
{
    /**
     * Файл html-обертки
     *
     * @var string
     */
    protected $wrapperName  = null;

    /**
     * Текущий хвост от запроса
     *
     * @var string
     */
    protected $uri          = '/';
    
    /**
     * @var Router
     */
    private $router;
    
    /**
     * @var Controller
     */
    protected $controller;

    static private $instance    = array();
    
    /**
     * Базовый путь к контроллеру
     * Переопределяется в наследниках
     * Например для главной: /
     * Для авторизации: /auth/
     *
     * @var string
     */
    protected $base         = '/';

    abstract public function header();
    abstract public function footer();
    abstract protected function initialization();

    /**
     * Каждая страница может иметь свою html-обертку
     * Если в классах-наследниках обертка $this->wrapperName
     * не задана, то используется TEMPLATE/wrapper.php
     *
     * @return string
     */
    final public function getWrapperName(){
        $file   = (isset($this->wrapperName)) ? $this->wrapperName : '/wrapper.php';
        $file   = '/' . trim($file, '/');
        return PATH_TEMPLATE . $file;
    }

    final public function setUri($uri = '/'){
        $uri        = '/'.trim($uri, '/');
        if (strpos($uri, '?') === false){
            $uri   .= (strpos($uri, '.', strrpos($uri, '/')) === false) ? "/" : "";
        }
        $uri        = str_replace('//', '/', $uri);
        $this->uri  = $uri;
    }

    final public function getUri($extra = array()){
        return $this->buildUrl($this->uri, $extra);
    }
    
    /**
     * Добавить к уже существующему урлу GET-параметры
     *
     */
    final public function buildUrl($uri, $extra = array()){
        $query      = (($pos = strpos($uri, '?')) !== false) ? substr($uri, $pos+1) : "";
        parse_str($query, $params);
        $params     = array_merge($params, $extra);
        $uri        = ($pos !== false) ? substr($uri, 0, $pos) : $uri;
        $uri       .= count($params) ? rtrim('?' . http_build_query($params), '?') : '';
        return $uri;
    }

    final public function getUriBase($extra = array()){
        return URL_PATH . ltrim($this->getUri($extra), '/');
    }

    final public function getBase(){
        return $this->base;
    }

    final public function getBackUrl(){
        return $this->getUri();
    }

    final public function getName(){
        return get_class($this);
    }
    
    final public function setRouter(Router $router){
        $this->router    = $router;
    }
    
    /**
     * @return Router
     */
    final public function getRouter(){
        if (!$this->router) PageException::pageRouterNotSelected();
        return $this->router;
    }
    
    
    
    final public function prepare(){
        try {
            $router = $this->getRouter();
        }
        catch (Exception $e){
            if ($e instanceof RouterException) throw $e;
//            throw $e;
            // XXX: Пока у нас еще остался функционал работы Page без участия Router.
            return false;
        }
        
        
        $this->controller = $router->getController();
    }
    
    final public function execute(){
        $router = $this->getRouter();
        try {
            RouterException::existControllerMethod($this->controller, $router->getMethod());
        }
        catch (RouterException $e){
            exception_log($e);
            PageException::pageInternalServerError();
        }
        
        $result = false;
        try {
            if ( ($get = SiteSkeleton::instance()->getGet()) && isset($get['debug']) && $get['debug'] == 1 ){
                dump(get_class($this->controller) . "->" . $router->getMethod() . '(/* ... */)');
            }
            $result = call_user_func_array(array($this->controller, $router->getMethod()), $router->getParams());
            $complete = true;
        }
        catch (Exception $e){
            $complete = false;
            $fe  = $e;
        }
        
        $max = 10;
        while ($complete === false){
            $max--;
            try {
                if ((($complete = $this->exception($e))) === false){
                    throw $e;
                }
            }
            catch (Exception $ee){
                if (get_class($e) === get_class($ee) && $e->getCode() == $ee->getCode()){
                    throw $ee;
                }
                
                if ($max < 0){
                    throw $ee;
                }
                
                $e = $ee;
            }
        }
        
        if ($complete !== true) throw $fe;
        return $result;
    }
    
    final public function draw(){
        $this->controller->draw();
    }
    
    public function isAction($actionName){
        $currentAction  = substr($this->uri, 0, ($pos = strpos($this->uri, '?')) ? $pos : strlen($this->uri));
//        return trim($currentAction, '/') === trim($this->base . $actionName, '/');
        return strpos($currentAction, $actionName) !== false;
    }

    /**
     * Построить урл.
     * Учитывается поле $base, параметр 'action'
     * обрабатывается особо
     *
     * @param array $params
     * @return string
     */
    public function makeUrl($params = array()){
        $url    = '/' .trim( $this->base, '/') . '/';
        $url    = str_replace('//', '/', $url);
        if (isset($params['action'])){
            $url   .= rtrim($params['action'], '/');
            $url    = rtrim($url, '/') . '/';
        }
        unset($params['action']);

        $uri    = '';
        if (!empty($params)){
            $urlParam   = array();
            foreach ($params as $key => $value) {
                if (0 === preg_match('/^[\w\d_\[\]]+$/', $key)){
                    throw new Exception('Wrong parameter key: '. var_export($key, true));
                }
            	$urlParam[]    = $key . "=" . urlencode($value);
            }

            $uri    = '?'.implode('&', $urlParam);
        }

        return $url . trim($uri, '/');
    }
    
    public function link($title){
        $params    = func_get_args();
        unset($params[0]);
        
        $url       = call_user_func_array('template_modify_link', $params);
        $title     = template_modify_terms($title, 'ссылка');
        return template_modify_href($url, $title);
    }
    
    public function redirect(){
        $params    = func_get_args();
        require_once(PATH_TEMPLATE_MODIFIERS . '/template_modify_link.php');
        $url       = call_user_func_array('template_modify_link', $params);
        PageException::pageRedirect($url);
    }
    
    /**
     * Подготовить рабочую страницу
     *
     * @param unknown_type $cName
     * @param unknown_type $uri
     * @return unknown
     */
    static final public function instance($cName, $uri){
        if (!isset(self::$instance[$cName])){
            self::$instance[$cName] = new $cName;
            self::$instance[$cName]->setUri($uri);
        }
        // Только одна страница может отображаться
        assert(count(self::$instance) === 1);

        return self::$instance[$cName];
    }
    
    public function exception(Exception $e){
        throw $e;
    }
    
    public function exception_initialization(Exception $e){
        throw $e;
    }
    
}

?>
