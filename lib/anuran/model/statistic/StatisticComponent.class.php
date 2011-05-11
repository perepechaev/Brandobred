<?php

require_once(PATH_MODEL . '/object/ObjectComponent.class.php');
require_once(PATH_MODEL . '/statistic/StatisticDataComponent.class.php');
require_once(PATH_MODEL . '/statistic/StatisticListComponent.class.php');

class StatisticComponent extends ObjectComponent
{
    public function __construct(){
        $this->setGiver(    new ObjectGiverComponent($this) );
        $this->setData(     new StatisticDataComponent($this) );
        $this->setAccessor( new ObjectAccessorComponent($this) );
        $this->setList(     new StatisticListComponent() );
    }

    public function visit($ip, $get, $agent){
        $data           = $this->getData();
        $data->ip       = $ip;
        $data->time     = date('Y-m-d H:i:s');
        $data->unique   = md5($agent . $ip);
        $data->get      = $get;
        Mysql::instance()->save($data);
        return $data;
    }

    /**
     * @return StatisticComponent
     */
    static public function create(){
        return new StatisticComponent();
    }
}


?>