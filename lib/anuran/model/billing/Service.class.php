<?php

require_once(PATH_CORE . '/MysqlData.class.php');
require_once(PATH_MODEL . '/billing/IBilling.interface.php');
require_once(PATH_MODEL . '/billing/ServiceGroup.class.php');

class Service extends MysqlData implements IBilling
{
    protected function make(){
        $this->field('id',          'int',  array('auto'));
        $this->field('group_id',    'int');
        $this->field('name',        'string', array('unique'));
    }

    /**
     * Осторожно! Магический метод!
     *
     * Способ обращения к функции
     * <code>
     * $obj = new Service();
     * echo $obj->capitalitylink;
     * </code>
     *
     * @return string
     */
    public function getCapitalityLink(){
        return PageBilling::create()->makeUrl(array('action'=>'service/capital', 'id' => $this->id));
    }

    public function loadFromData($data){
        $this->id           = $data[0];
        $this->group_id     = $data[1];
        $this->name         = $data[2];
        return clone ($this);
    }

    public function capital(){
        $name   = $this->name;


        mb_internal_encoding('UTF-8');
        preg_match_all('/([\wа-я]+)/ui', $name, $matches, PREG_OFFSET_CAPTURE);
        $words  = array();
        foreach ($matches[1] as $w){

            $byteStr = mb_strcut($name, 0, $w[1], 'latin1');
            $chrLen  = mb_strlen($byteStr, 'UTF-8');
            if ($w[1] > $chrLen)
                $w[1] = $chrLen;
            $words[$w[1]]   = $w[0];
        }

        foreach ($words as $pos => $word){
            $letter = mb_substr($word, 0, 1);
            $cap    = mb_strtoupper($letter) === $letter;

            if ($cap){
                $word   = mb_strtolower($letter) . mb_substr($word, 1);
            }
            else {
                $word   = mb_strtoupper($letter) . mb_substr($word, 1);
            }

            $name   = mb_substr($name, 0, $pos) . $word . mb_substr($name, $pos + mb_strlen($word));
        }

        $this->name = $name;
    }

    public function store(){
        Mysql::instance()->update($this);
    }

    static public function listAll(){
        Mysql::instance()->select('', new Service());
        return Mysql::instance()->fetch(new Service);
    }

    /**
     * @param unknown_type $id
     * @return unknown
     */
    static public function prepareById($id){
        $service        = new Service();
        $service->id    = $id;
        Mysql::instance()->select('WHERE id=:id:', $service);
        Mysql::instance()->fetch($service, $list);
        equal($list->count() === 1, 'Ожидается одна запись в базе данных');
        return $list->getIterator()->current();
    }


    public function createList(){
        return new MysqlList();
    }
}
