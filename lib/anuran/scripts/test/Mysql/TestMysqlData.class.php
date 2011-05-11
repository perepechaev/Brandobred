<?php

require_once(PATH_CORE . '/Mysql.class.php');
require_once(PATH_CORE . '/MysqlData.class.php');

class TestMysqlData extends MysqlData{
    
    public $tested;
    
    protected function make(){
        $this->field('service_id',  'int', array('request', 'unsigned'));
        $this->field('abonent_id',  'int', array('request', 'unsigned'));
        $this->field('date',        'date', array('request'));
        $this->field('amount',      'decimal', array('request', 'unsigned','length' => 12, 'dec' => 2));
        $this->field('comment',     'string', array('request', 'length'=>655));
        $this->field('title',       'string', array('request', 'length'=>655));
        $this->field('post',        'string', array('length'=>655));
        $this->field('dateNull',    'date', array());
        
        $this->name('test_mysql_data');
        
    }
    
    public function prepare(){
        $this->tested   = 'prepare is execute';
        
        return parent::prepare();
    }
    
    public function setPost($post){
        return "Realy set post: " . $post;
    }
}

class TestMysqlDataDelete extends TestMysqlData{
    
    protected  function make(){
        parent::make();
        $this->field('id',  'int', array('unsigned', 'auto'));
        $this->name('test_mysql_data_delete');
    }
    public function prepare(){
        $this;
        return parent::prepare();
    }
}

class TestMysqlDataAlterTable extends TestMysqlData
{
    protected function make(){
        parent::make();
        
        $this->refield('post',  'int', array('request'));
        $this->field('alter',   'datetime');
    }
}

class TestMysqlDataAlias extends TestMysqlData
{
    protected function make(){
        parent::make();
        
        $this->alias('title', 'question');
    }
    
    public function setPost($post){
        return $post;
    }
}




?>