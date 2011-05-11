<?php

require_once(PATH_CORE . '/Mysql.class.php');
require_once(PATH_MODEL . '/billing/IBilling.interface.php');
require_once(PATH_MODEL . '/billing/Service.class.php');
require_once(PATH_MODEL . '/billing/BillingList.class.php');

class Billing extends MysqlData implements IBilling
{
    protected function make(){
        $this->field('service_id',  'int', array('request', 'unsigned'));
        $this->field('abonent_id',  'int', array('request', 'unsigned'));
        $this->field('date',        'date', array('request'));
        $this->field('amount',      'decimal', array('request', 'unsigned','length' => 12, 'dec' => 2));
        $this->index(array('service_id'));
        $this->index(array('service_id', 'date'));
    }

    public function loadFromData($data){
        $this->service_id   = $data[0];
        $this->abonent_id   = $data[1];
        $this->date         = $data[2];
        $this->amount       = $data[3];
        return clone ($this);
    }

    /**
     * Здесь не опечатка, действительно, есть класс
     * BillingList, но он отвечает за загрузку
     * информации с txt-файлов. А здесь MysqlList
     * вполне пока подходит
     *
     * NB: что-то у меня их черезчур много... не
     * хватает класса List для полного счастья :)
     *
     * @return MysqlList
     */
    public function createList(){
        return new MysqlList();
    }

}
