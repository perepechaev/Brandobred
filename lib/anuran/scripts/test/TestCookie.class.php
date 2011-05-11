<?php


class TestCookie extends Cookie {

    private $testCookie;
    
    public function set($name , $value , $date = 0){

        
        $this->testCookie[$name]  = $value;
        
        return $this;
    }
    
    public function is($name){
        return isset($this->testCookie[$name]);
    }
    
    public function get($name){
        return $this->testCookie[$name];    
    
    }
    
    public function delete($name){
        equal($this->is($name), 'Попытка удаления куков, которые не были установлены');
        unset($this->testCookie[$name]);
    }
    
    /**
     * @return TestCookie
     */
    static public function create(){
        return new self();
    }
}



?>