<?php

class Validator extends Exception
{

    const VALIDATE_EMPTY            = 1;
    const VALIDATE_MAX_LENGHT       = 2;
    const VALIDATE_MIN_LENGHT       = 3;
    const VALIDATE_NUMBER           = 4;
    const VALIDATE_DATE_FORMAT      = 5;
    const VALIDATE_UNCORRECT        = 6;
    const VALIDATE_NON_SET          = 7;
    const VALIDATE_UNIQU            = 8;
    const VALIDATE_AGE              = 9;
    const VALIDATE_COMPARE_WRONG    = 10;
    const VALIDATE_POSITIVE_NUMBER  = 11;
    
    const VALIDATE_USER_POST        = 800;
    
    
//    const VALIDATE_EMPTY            = 1;
//    const VALIDATE_MAX_LENGHT       = 2;
//    const VALIDATE_MIN_LENGHT       = 3;
//    const VALIDATE_UNCORRECT        = 6;
//    const VALIDATE_NON_SET          = 7;
    
    
    protected $post       = array();
    public  $errors   = array();
    
    protected  function init($post){
        $this->post = $post;
        $this->errors = array();
    }
        
    public function testEmpty($array){
        
        foreach ($array as $key => $field) {
            if (empty($this->post[$field])){
                $this->errors[$field][] = self::VALIDATE_EMPTY;
            }
        }
        
//        return empty($this($this->errors[$field]));
    }

        
    public function testIsset($array){
        foreach ($array as $key => $field) {
            
            if (!isset($this->post[$field])){
                $this->errors[$field][] = self::VALIDATE_NON_SET;
            }
        }
        
//        return empty($this($this->errors[$field]));
    }
    
    protected function mandatoryValidate($field){
        if (empty($this->errors[$field])){
            return true;
        }
        foreach ($this->errors[$field] as $key=>$value){
            if ($value ===  self::VALIDATE_EMPTY || $value === self::VALIDATE_NON_SET){
                return false;
            }
        }
        return true;
        
    }
    
    protected function validateMaxLenght($field, $long){
        if (mb_strlen($this->post[$field]) > $long){
            $this->errors[$field][] = self::VALIDATE_MAX_LENGHT; 
        }
    }
    
    protected function validateMinLenght($field, $long){
        if (mb_strlen($this->post[$field]) < $long){
            $this->errors[$field][] = self::VALIDATE_MIN_LENGHT; 
        }
    }
    
    public function validateNumerick($field, $name){
        if (!empty($field) && !is_numeric($field)){
            $this->errors[$name][] = self::VALIDATE_NUMBER;
        }
    }
     
    
    public function validatePositiveNumerick($field, $name){
        if (!empty($field) && !is_numeric($field) || $field<0){
            $this->errors[$name][] = self::VALIDATE_POSITIVE_NUMBER;
        }
    }
     
    public function collectErrors($e){
        if (count($e->errors) > 0){
            throw $e; 
        }
    }

    
    public function getErrors(){
        equal(!empty($this->errors), "Попытка получить ошибки валидации при отсутствии ошибок");
        return $this->errors;
    }
    
    /**
     * Enter description here...
     *
     * @param unknown_type $post
     * @param unknown_type $message
     * @param unknown_type $code
     * @return Validator
     */
    static public function create($post, $message, $code){
        $e      = new self($message, $code);
        $e->init($post);
        return $e;
    }
    
}
?>