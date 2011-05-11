<?php

class Terms
{
    static private $instance;
    
    private $terms = array();
    
    private function __construct(){}
    
    public function add($title, $variants){
        equal(!isset($this->terms[$title]), "'$title' уже добавлен в Terms: " . var_export($this->terms, true));
        $this->terms[$title] = $variants;
    }
    
    /**
     * @param $title    string
     * @return array
     */
    public function get($title, $value){
        equal(isset($this->terms[$title]));
        return isset($this->terms[$title][$value]) ? $this->terms[$title][$value] : $value;
    }
    
    public function getVariants($title){
        equal($this->is($title));
        return $this->terms[$title];
    }
    
    public function is($title){
        return isset($this->terms[$title]);
    }
    
    /**
     * @return Terms
     */
    static public function instance(){
        if (!isset(self::$instance)){
            self::$instance = new self();
        }
        
        return self::$instance;
    }
}

function termsJs($name){
    $name = addslashes($name);
    $variants = Terms::instance()->getVariants($name);
    foreach ($variants as $key => &$text){
        $text = "{value: '$key', text: '". terms($key, $name) ."'}";
    }
    $values = implode(',', $variants);
    return <<<JS
    {
        name: '$name', 
        values: [
            $values
        ]
    
    }
JS;
}

?>