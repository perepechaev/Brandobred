<?php

class Layout extends Controller
{
    /**
     * @var Template
     */
    private $template;
    
    /**
     * @var Controller
     */
    private $controller;
    
    public function __construct(Template $template, Controller $controller){
        $this->template     = $template;
        $this->controller   = $controller;
    }
    
    public function __call($name, $args){
        ob_start();
        call_user_func_array(array($this->template, $name), $args);
        $content = ob_get_clean();
        $this->controller->addHtml($content);        
    }
    
    public function wrapper($name = ''){
        
        $this->template->wrapper( $this->controller );
    }
}

?>