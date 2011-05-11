<?php

class Session
{
    static $instance;
    
    private function __construct(){
        session_start();
    }
    
    /**
     * @return Session
     */
    static public function need(){
        if (!self::$instance){
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Автоматический запуск сессии при обнаружении куки
     * 
     * @return bool
     */
    static public function auto(){
        if (self::$instance){
            return true;
        }
        
        if (isset($_COOKIE[session_name()])){
            self::need();
            return true;
        }
        
        return false;
    }
    
    static public function destroy(){
        Session::need();
        
        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();        
        
    }
    
    static public function instance(){
        return self::need();
    }
}

?>