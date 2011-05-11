<?php

require_once(PATH_CORE . '/Mysql.class.php');
require_once(PATH_CORE . '/MysqlData.class.php');

require_once(PATH_CORE . '/TextFormatted.class.php');
require_once(PATH_CORE . '/DateFormatted.class.php');

class ArticleDataComponent extends ObjectDataComponent
{
    static protected $tableName   = 'article';
    
    protected function make(){
        parent::make();
        $this->field('text',        'string',   array('request', 'length'=>65536));
        
        $this->name(self::$tableName);
        
        equal(isset($this->component), "Не выбрана компонента");
    }
    
    public function __get($key){
        if ($key == 'url'){
            return $this->geturl();
        }
        return parent::__get($key);
    }
    
    public function geturl(){
        return Router::instance()->makeUrl(array('articles', $this->id));
    }
    
    public function delete(){
        $this->status = ArticleComponent::STATUS_DELETE;
        $this->save();
    }
    
    // XXX: Перенести Методы доступа к объекту => Accessor 


    static public function setTableName($name){
        // XXX: Если не используется, то убить этот метод
        // Используется в ArticleDataComponent, вопрос, оправдано ли?
        assert(false);
        self::$tableName    = $name;
    }
    
}