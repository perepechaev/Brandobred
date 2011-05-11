<?php

require_once(PATH_MODEL . '/object/ObjectComponent.class.php');

require_once(dirname(__FILE__) . '/ArticleDataComponent.class.php');
require_once(dirname(__FILE__) . '/ArticleListComponent.class.php');

class ArticleComponent extends ObjectComponent
{
    /**
     * @var Template
     */
    static public $template;
    
    /**
     * @return ArticleDataComponent
     */
    public function getData(){
        return parent::getData();
    }

    /**
     * @return ArticleListComponent
     */
    public function getList(){
        return parent::getList();
    }

    /**
     * @return ArticleComponent
     */
    static public function create(){
        $component = new ArticleComponent();
         
        $component->setData( new ArticleDataComponent($component) );
        $component->setTemplate(self::$template );
        $component->setAccessor( new ArticleAccessorComponent($component));
         
        return  $component;
    }

}

class ArticleAccessorComponent extends ObjectAccessorComponent
{
    
}


?>