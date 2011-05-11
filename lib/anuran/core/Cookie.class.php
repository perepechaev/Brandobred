<?php

class Cookie{

    public function set($name , $value , $date = 0){
        setcookie($name, $value, $date, URL_PATH, URL_SITE);
        $_COOKIE[$name] = $value;
        return $this;
    }
    
    public function is($name){
        return isset($_COOKIE[$name]);
    }
    
    public function get($name){
        equal($this->is($name));
        return ($_COOKIE[$name]);
    }
    
    public function delete($name){
        equal($this->is($name));
        unset($_COOKIE[$name]);
        setcookie($name, "", -1, '/');
    }
}

?>