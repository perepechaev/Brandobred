<?php

class MysqlDataOne extends MysqlData
{
    protected function make(){
        $this->field('id',          'int',      array('request', 'unsigned', 'auto'));
        $this->field('title',       'string',   array('request'));
        $this->name('data_one');

//        $this->reference('refMany', 'many', 'MysqlDataMany', 'parent_id');
    }

    public function getById($id){
        $this->id   = $id;
        Mysql::instance()->get($this, null, 'WHERE `id`=:id: LIMIT 1');
    }

    /**
     * @return MysqlList
     */
    public function createList(){
        return new MysqlList();
    }

}

?>