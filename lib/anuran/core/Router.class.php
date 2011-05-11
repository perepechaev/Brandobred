<?php

require_once PATH_MODEL . '/file/FileException.class.php';
require_once PATH_CORE . '/RouterException.class.php';

abstract class Router
{
    /**
     * @var Router
     */
    static private $instance;
    
    /**
     * Пусть к клиенту маршрутизатора
     * 
     * @var string
     */
    static private $path;
    
    /**
     * Текущий url
     * 
     * @var string
     */
    static private $url;
    
    /**
     * Структурированный url
     * 
     * @var array
     */
    private $urlStructure;
    
    static private $urlPath;
    
    /**
     * Правила сайта
     * 
     * @var array
     */
    protected $rule    = array();
    
    /**
     * @var array
     */
    protected $rewrite = array();
    
    private $ruleStructure;
    
    private $pageName;
    
    private $method;
    
    private $params;
    
    private $controllers = array();
    
    protected function __construct(){
        $this->rule();
        $this->parse();
    }
    
    abstract protected function rule();
    
    /**
     * @return Controller
     */
    final public function getController(){
        $class_name    = $this->ruleStructure[0];
        if (!class_exists($class_name)){
            FileException::isReadable( PATH_CONTROLLER . "/$class_name.class.php" );
            require_once PATH_CONTROLLER . "/$class_name.class.php";
        }
        
        if (empty($this->controllers[$class_name])){
            $this->controllers[$class_name] = call_user_func(array($class_name, 'create'));
        }
        
        return $this->controllers[$class_name];
    }
    
    final public function getPageName(){
        if (isset($this->rule[$this->urlStructure[0]])){
            return $this->urlStructure[0];
        }
        else
            return ROUTER_ROOT;
    }
    
    final public function getMethod(){
        return $this->ruleStructure[1];
    }
    
    final public function setMethod($method){
        $this->ruleStructure[1] = $method;
    }
    
    final public function getParams(){
        if (!empty($this->ruleStructure[2])){
            if (is_string($this->ruleStructure[2])){
                $params = ltrim( $this->ruleStructure[2], '%');
                return $this->urlStructure[$params - 1];
            }
            elseif (is_array($this->ruleStructure[2])){
                $params = $this->ruleStructure[2];
                foreach ($params as &$param){
                    if (is_string($param)){
                        $param = ltrim( $param, '%' );
                        $param = $this->urlStructure[ $param - 1 ];
                    }
                }
                return $params;
            }
            else {
                return $this->ruleStructure[2];
            }
        }
        return array();
    }
    
    private function parse(){
        $this->parseUrl();
        
        $rule    = $this->rule;
        foreach ($this->urlStructure as $dep => & $name){
            if (!isset($rule[$name])) {
                foreach ($rule as $key => $item){
                    switch ($key) {
                    	case ROUTER_HTML:
                    	    if (preg_match('/^([\wа-яё\s' . preg_quote('."#\\\'-?<>$!@%“”','/') . ']+)\.html$/ui', $name, $params) !== 0){
                    	        $rule    = $rule[ ROUTER_HTML ];
                    	        $name    = $params[1];
                    	    }
                	    break 3;
                    	    
                    	case ROUTER_DECIMAL:
                    	    if (preg_match('/^(\d+)$/', $name, $params) !== 0){
                    	        $rule    = $rule[ ROUTER_DECIMAL ];
                    	        $name    = $params[1];
                        	    if (isset($rule[3]) && (count($this->urlStructure) !== $dep + 1)){
                        	        $rule    = $rule[3];
                        	    }
                    	        elseif ((count($this->urlStructure) === $dep + 1) ){
                        	        break 3;
                        	    }
                    	    }
                    	break 2;
                    	    
                    	case ROUTER_STRING:
                    	    if (preg_match('/^([\wа-яё\s' . preg_quote('#."\'-?<>$!@%“”','/') . ']+)$/ui', $name, $params) !== 0){
                    	        $rule    = $rule[ ROUTER_STRING ];
                    	        $name    = $params[1];
                    	    }
                    	    
                    	    if (isset($rule[3]) && (count($this->urlStructure) !== $dep + 1)){
                    	        $rule    = $rule[3];
                    	    }
                    	    elseif ((count($this->urlStructure) === $dep + 1) ){
                    	        break 3;
                    	    }
                    	break 2;
                    	                        	                        	
                    	default:
                    	break;
                    }
                }
                if (count($this->urlStructure) === $dep + 1) {
                    RouterException::notFoundMethod( Router::$url, get_class($this), $name );
                }
            }
            elseif (isset($rule[$name])) {
                $rule    = $rule[$name];
                if (count($this->urlStructure) !== $dep + 1 && isset($rule[3])) {
                    $rule    = $rule[3];
                }
                elseif ( count($this->urlStructure) !== $dep + 1) {
                    RouterException::notFoundMethod( Router::$url, get_class($this), $name );
                }
            }
            else{
                RouterException::notFoundMethod( Router::$url, get_class($this), $name );
            }
        }

        if (isset($rule[3])){
            unset($rule[3]);
        }
        
        if (empty($rule[0])) {
            RouterException::notFoundMethod(Router::$url, get_class($this));
        }
        if (empty($rule[1])) {
            RouterException::notFoundMethod(Router::$url, get_class($this));
        }
        
        $this->ruleStructure = $rule;
    }
    
    private function parseUrl(){
        if (($pos = mb_strpos(Router::$url, '?')) === false){
            $url = Router::$url;
        }
        else {
            $url = mb_substr(Router::$url, 0, $pos);
        }
        
        $url = mb_strcut($url, mb_strlen(self::$urlPath));
        
        $url = trim($url, '/');
        
        if ($url === '') {
            $this->urlStructure    = array(ROUTER_ROOT);
        }
        else {
            $this->urlStructure    = explode('/', $url);
            foreach ($this->urlStructure as &$dep){
                $dep    = urldecode($dep);
            }
        }
        
        if ($key = array_search($this->urlStructure[0], $this->rewrite)){
            $this->urlStructure[0] = $key;
        }
    }
    
    public function getUrlStructure(){
        assert(defined('TESTING_RUN'));
        return $this->urlStructure;
    }
    
    public function getRule(){
        return $this->rule;
    }
    
    static public function setPathClient($path){
        Router::$path   = $path;
    }
    
    static public function setUrl($url){
        Router::$url    = $url;
        if (isset(Router::$instance)){
            Router::instance()->parse();
        }
    }
    
    static public function setUrlPath($urlPath){
        self::$urlPath = $urlPath;
    }
    
    public function getUrl(){
        return self::$url;
    }
    
    public function makeUrl($params){
        if (is_string($params)){
            $params    = explode('/', trim($params, '/'));
        }
        if (is_array($params)){
            $tmp_params    = array();
            foreach ($params as $param){
                $tmp_params = array_merge($tmp_params, explode('/', trim($param, '/')));
            }
            $params    = $tmp_params;
        }
        
        $last    = '/';
        $rule    = $this->rule;
        foreach ($this->rewrite as $item => $value){
            $rule[$value]    = $rule[$item];
        }
        
        foreach ($params as & $param){

            // Построение урлов оканчивающихся на html
            if (!isset($rule[$param]) && isset($rule[ROUTER_HTML])){
                $last = '.html';
            }
            else {
                if (isset($rule[$param])){
                    $rule    = $rule[$param];
                }
                elseif (isset($rule[ROUTER_STRING])){
                    $rule    = $rule[ROUTER_STRING];
                }
                $last    = '/';
            }
            
            if (isset($rule[3])) $rule = $rule[3];
            
            // Используем переопределение страниц
            if (isset($this->rewrite[ $param ])){
                $param = $this->rewrite[ $param ];
            }
            
            $param = str_replace('+', ' ', $param);
            $param = str_replace('+', '%20', urlencode($param));
        }
        
        $path   = trim(implode('/', $params), '/');
        
        $url    = self::$urlPath . ($path ? $path . $last : '');
        return $url;
    }
    
    
    /**
     * 
     * @param string $class_name
     * @return Router
     */
    static public function instance( $class_name = null ) {
        if ( !isset(Router::$instance)){
            
            if ( !isset(Router::$path) )   RouterException::notSetPathToClient();
            if ( !isset(Router::$url) )    RouterException::notSetUrl();
            if ( !isset($class_name))      RouterException::notSelectClient();
            
            Router::$urlPath    = isset(Router::$urlPath) ? Router::$urlPath : '/';
            
            $filename    = Router::$path . "/$class_name.class.php";

            if (!class_exists($class_name)){
                if ( !is_readable($filename) ) RouterException::notFindClientFile($filename);
                require_once $filename;
            }
            Router::$instance = new $class_name;
            
        }
        else {
            if ($class_name) {
                RouterException::clientAlredySelected();
            }
        }
        
        assert( isset(Router::$instance) );
        assert( Router::$instance instanceof Router );
        
        return Router::$instance;
    }
    
    static public function setInstance(){
        assert(false);
    }
}
?>