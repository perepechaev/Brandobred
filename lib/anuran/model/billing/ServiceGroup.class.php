<?php

require_once(PATH_CORE . '/MysqlData.class.php');
require_once(PATH_MODEL . '/billing/IBilling.interface.php');

class ServiceGroup extends MysqlData implements IBilling
{
    protected function make(){
        $this->name('service_group');
        $this->field('id',      'int', array('auto'));
        $this->field('name',    'string', array('length' => 128, 'request'));
    }

    public function loadFromData($data){
        $this->id   = $data[0];
        $this->name = $data[1];
        return clone ($this);
    }

    static public function listAll(){
        Mysql::instance()->select('', $serive = new ServiceGroup());
        return Mysql::instance()->fetch($serive);
    }

    public function createList(){
        return new MysqlList();
    }
}
