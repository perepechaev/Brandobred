<?php

require_once(dirname(__FILE__) . '/TemplateException.class.php');

class Template
{
    private $controller   = null;

    final public function setController(Controller $c){
        $this->controller   = $c;
    }

    /**
     * @return Controller
     */
    final public function getController(){
        equal(isset($this->controller), 'Контроллер не установлен для класса ' . get_class($this));
        return $this->controller;
    }

    public function getAction($actionName, $param = null){
        TemplateException::templateMethodNotFound($this, $actionName);
        ob_start();
        if (isset($param)){
            $this->$actionName($param);
        }
        else {
            $this->$actionName();
        }
        $content    = ob_get_clean();
        return $content;
    }
    
    public function get($name, $params = array()){
        return $this->getAction($name, $params);
    }
}

?>