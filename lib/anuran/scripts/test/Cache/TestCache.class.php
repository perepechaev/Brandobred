<?php

require_once(dirname(__FILE__) . '/../TestHead.php');

class TestCache extends Test
{
    static private $countGetObject  = 0;
    private $store;
    private $dir_name;

    public function test_constructor(){
        Cache::initialization($this->dir_name = PATH_CACHE . '/test/');
        equal(is_dir($this->dir_name), 'Каталог не создан: ' . $this->dir_name);

        $this->result('Test Cache constructor', 'ok');
    }

    public function test_generateName(){
        $cache      = new Cache();
        $cached     = new TestCachedClass();

        $res1       = $cache->generateFileName('test_1', array(&$cached, 'execute'), 1);
        $res2       = $cache->generateFileName('test_1', array(&$cached, 'execute'), 1);

        equal($res1 === $res2);

        $this->result('Test Cache generate name', 'ok');
    }

    public function test_store(){
//        $this->detail   = true;

        $cached     = new TestCachedClass();
        $res1       = Cache::execute('test_1', array(&$cached, 'execute'), 1);
        $res2       = Cache::execute('test_2', array(&$cached, 'execute'), 2);

        equal(TestCachedClass::$inc === 2, 'Ожидается 2, получено '. TestCachedClass::$inc);

        $res3       = Cache::execute('test_1', array(&$cached, 'execute'), 1);
        equal(TestCachedClass::$inc === 2, 'Ожидается 2, получено '. TestCachedClass::$inc);
        equal($res3 === 1, 'Ожидается 1, получено : ' . var_export($res3, true) . __LINE__);

        unlink($this->dir_name . 'test_1/'.Cache::create()->generateFileName('test_1', array(&$cached, 'execute'), 1));
        unlink($this->dir_name . 'test_2/'.Cache::create()->generateFileName('test_2', array(&$cached, 'execute'), 2));
        rmdir($this->dir_name . 'test_1');
        rmdir($this->dir_name . 'test_2');

        $this->result('Test Cache store', 'ok');
    }

    public function test_cacheObject(){
        $cached     = new TestCachedClass();

        $obj        = Cache::execute('simple', array(&$cached, 'getObject'));

        equal($obj == $cached, 'Не смогли получить из кэша объект с теми же полями');
        equal($obj !== $cached, 'Получили тот же объект... не есть гуд');

        unlink($this->dir_name . 'simple/'.Cache::create()->generateFileName('simple', array(&$cached, 'getObject')));

        $cached->param  = rand(0, 1000);
        $param          = $cached->param;
        $obj            = Cache::execute('simple_param', array(&$cached, 'getObject'));
        equal($obj == $cached, __LINE__);

        $cached         = new TestCachedClass();
        $cached         = Cache::execute('simple_param', array(&$cached, 'getObject'));

        //NB: тут интересное решение, имя кэшируемого файла зависит от объекта
        equal($cached->param !== $param, __LINE__);

        unlink($this->dir_name . 'simple_param/'.Cache::create()->generateFileName('simple_param', array(&$cached, 'getObject')));
        $cached->param  = $param;
        unlink($this->dir_name . 'simple_param/'.Cache::create()->generateFileName('simple_param', array(&$cached, 'getObject')));

        rmdir($this->dir_name . 'simple');
        rmdir($this->dir_name . 'simple_param');

        $this->result('Test Cache object', 'ok');
    }

    public function test_cacheArray(){
    }

    public function test_cacheBinary(){

    }

    public function __destruct(){
        rmdir($this->dir_name);
    }

}

class TestCachedClass
{
    static public $inc = 0;

    public $param;

    public function execute($param){
        self::$inc++;
        return $param;
    }

    public function getObject(){
        return $this;
    }

    public function getArray(){
        return array(1, 2, 5, array(3, 4));
    }
}

$test   = new TestCache();
$test->complete();

?>