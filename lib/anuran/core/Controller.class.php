<?php

abstract class Controller
{
    private $html               = null;
    private $template           = null;
    private $model              = null;

    protected $module           = '';
    
    /**
     * @var ObjectComponent
     */
    private $component        = null;
    
    protected $post;
    
    protected $get;
    

    protected function __construct(){
        if (class_exists('SiteSkeleton')){
            $this->post = SiteSkeleton::instance()->getPost();
            $this->get  = SiteSkeleton::instance()->getGet();
        }
    }
    
    final public function draw(){
        echo $this->html;
        unset($this->html);
    }

    final public function __toString(){
        return $this->html;
    }

    final protected function setHtml($html){
        $this->html = $html;
    }
    
    final protected function addHtml($html){
        if (is_object($html) && ($html instanceof Controller )){
            $this->addHtml( $html->getHtml() );
        }
        elseif (is_string($html)){
            $this->html .= $html;
        }
        else {
            assert(false);
        }
    }

    final public function getHtml(){
        return $this->html;
    }

    final protected function setTemplate(Template $template){
        $this->template = $template;
    }

    /**
     * @return Template
     */
    final protected function getTemplate(){
        return $this->template;
    }

    /**
     * @param ObjectComponent $obj
     * @return Controller
     */
    final protected function setComponent(ObjectComponent $obj){
        $this->component    = $obj;
        $obj->setController($this);
        return $this;
    }
    
    /**
     * @return ObjectComponent
     */
    public function getComponent(){
        assert(isset($this->component));
        return $this->component;
    }
    
    final protected function addBlock($templateName, $templateParam = array()){
        $this->addHtml(
            $this->getTemplate()->get($templateName, $templateParam)
        );
    }
    
    final protected function setBlock($templateName, $templateParam = array()){
        $this->setHtml(
            $this->getTemplate()->get($templateName, $templateParam)
        );
    }

    /**
     * @return Model
     */
    final protected function getModel(){
        assert(false);
        if (is_null($this->model)){
            // XXX: Проверка на существование файла
            $class_name = strtoupper(substr($this->module, 0, 1)) . substr($this->module, 1);
            $filename   = PATH_MODEL . '/' . $this->module . '/' . $class_name . '.class.php';
            assert(file_exists($filename));
            require_once($filename);
            // XXX: Проверка на существование класса
            $cName  = $this->module;
            $this->model    = new $cName();
        }
        return $this->model;
    }

    public function __clone(){
        $this->html = "";
    }

    /**
     * @param   string  $action
     * @param   array   $param
     * @return  GalleryComponent
     */
    public function prepareAction($action, $param){
        assert(false);
        $contr      = $this;
        $method     = 'action' . $action;
        equal(method_exists($contr, $method), "В объекте '".get_class($contr)."' не найден метод '$method'");

        call_user_func_array(array($contr, $method), $param);
        return $contr;
    }
    
    /**
     * @param string path url
     */
    protected function redirect($route, $params = array()){
        $url       = Router::instance()->makeUrl($route);
        $url      .= $params ? '?' . implode('&', (array) $params) : '';
        PageException::pageRedirect($url);
    }
    
    public function __call($method, $params){
        equal(false, 'В контроллере ' . get_class($this) . ' не найден метод ' . $method . '()');
    }

}

?>