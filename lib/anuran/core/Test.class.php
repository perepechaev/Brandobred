<?php

class Test
{
    static private $encode  = '';

    protected $detail       = false;
    protected $marktime     = true;
    protected $useBuffer    = true;
    protected $clearDB      = false; 
    
    protected $lasttime     = false;
    
    static protected $handler = array();

    private $detail_def;

    final public function complete(){
        if (empty(self::$encode)){
            self::$encode = TEST_OUTPUT_ENCODE;
        }
        if ($this->useBuffer) ob_start();
        
        if (isset(self::$handler['oncomplete'])){
            call_user_func(self::$handler['oncomplete'], get_class($this));
        }
        
        $methods    = get_class_methods($this);
        foreach ($methods as $methodName){
            if (substr($methodName, 0, 5) === 'test_'){
                $this->run($methodName);
            }
        }
        
        if (isset(self::$handler['onflush'])){
            call_user_func(self::$handler['onflush']);
        }
        
        if ($this->useBuffer) ob_end_flush();
    }
    
    static public function setHandler($name, $call_back){
        self::$handler[$name] = $call_back;
    }

    final public function run($method){
        if (empty(self::$encode)){
            self::$encode = TEST_OUTPUT_ENCODE;
        }
        $this->lasttime = microtime(1);
        try{
            $this->$method();
        }
        catch (Exception $e){
            if ($this->detail || $this->detail_def){
                $this->detailResult($e);
            }
            else {
                $this->error($e, false, false, $method);
            }
            $this->detail_def   = $this->detail;
        }
    }

    final public function result($text, $result){
        $text   = mb_convert_encoding($text, self::$encode, 'UTF-8');
        $result = mb_convert_encoding($result, self::$encode, 'UTF-8');
        $text   = str_pad($text . ':', 40, ' ');

        $t      = "";
        if ($this->marktime) {
            $time   = $this->lasttime;
            $this->lasttime = microtime(1);
            $t      = $this->lasttime - $time;
            $t      = number_format($t, 4, '.', ' ');
            $t      = "({$t}s)\t";
        }

        echo $text . $t . $result . "\n";
    }

    final public function detail($bool){
        $this->detail_def = $bool;
    }

    final public function error(Exception $e, $line = false, $detail = false, $method = false){
        if ($detail){
            $this->detailResult($e->getCode(), $e->getMessage(), $e->getTraceAsString());
        }
        else {
            $line   = ($line) ? "($line line) " : "";
            $method = ($method) ? $method : 'Unknow test';
            $this->result($method . ' ' . get_class($e) . '(' . $e->getCode() . ')', "ERROR $line". $e->getMessage());
        }
    }

    final public function detailResult(Exception $e){
        $code       = $e->getCode();
        $message    = $e->getMessage();
        $detail     = $e->getTraceAsString();
        $class      = get_class($e);
        echo "\t$class($code) " . mb_convert_encoding($message, self::$encode, 'UTF-8') . "\n";
        echo "\t" . str_replace("\n", "\n\t", mb_convert_encoding($detail, self::$encode, 'UTF-8')) . "\n";
    }

    static final public function setEncode($encode){
        self::$encode   = $encode;
    }
    
    /**
     * @param $className string
     * @return Test
     */
    static final public function create($className){
        try{
            if (!class_exists($className)) equal(false, 'Класс не найден');
            $obj = new $className();
            return $obj;
        }
        catch (Exception $e){
            echo "\n\nFATAL ERROR: $className:\n\n";
            self::create('Test')->detailResult($e);
            die();
        }
    }
}


?>