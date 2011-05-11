<?php

require_once(dirname(__FILE__) . '/../TestHead.php');
require_once(PATH_CORE . '/Mysql.class.php');

require_once(dirname(__FILE__) . '/MysqlDataOne.php');
require_once(dirname(__FILE__) . '/MysqlDataMany.php');

if (!class_exists('UserTemplate')){
    class UserTemplate extends Template{}
}

class TestMysqlReference extends Test
{
    protected $detail   = true;

    /**
     * @var Mysql
     */
    private $mysql      = null;

    public function __construct(){
        $this->mysql    = Mysql::instance('TestMysqlReference');
        $this->mysql->setTablePrefix    ('test_reference__');
        $this->mysql->createTable( new MysqlDataOne() );
        $this->mysql->createTable( new MysqlDataMany() );
    }

    public function test_prepareData(){
        $one            = new MysqlDataOne();
        $one->id        = 1;
        $one->title     = 'One 1';
        $this->mysql->insert($one);

        $one->id        = 2;
        $one->title     = 'One 2';
        $this->mysql->insert($one);

        $this->result("Prepare Data", 'ok');
    }

    public function test_getReferenceData(){
        $this->mysql->selectRef(array());

        $this->result("Ger ReferentData", 'ok');
    }

    public function __destruct(){
        $this->mysql->dropTable(new MysqlDataOne());
        $this->mysql->dropTable(new MysqlDataMany());
    }
}
$test   = new TestMysqlReference();
$test->complete();

?>