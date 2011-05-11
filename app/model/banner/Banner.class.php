<?php

class BannerPlace
{
    const RIGHT         = 'right';
    const BOTTOM        = 'bottom';
    
    static public function getAvailable(){
        return array(self::RIGHT, self::BOTTOM);
    }
}

class Banner extends MysqlData
{
    public function make(){
        $this->field('id',          'int',      array('auto'));
        $this->field('hits',        'int',      array('request', 'default' => 0));
        $this->field('conversion',  'int',      array('request', 'default' => 0));
        $this->field('remote_url',  'string',   array('request'));
        $this->field('filename',    'string',   array('request'));
        $this->field('place',       'enum',     array(
            'request', 
            'default'   => BannerPlace::RIGHT, 
            'values'    => BannerPlace::getAvailable()
        ));
        
        $this->name('banner');
    }
    
    public function expectOneRecord($count){
        if ($count == 0){
            return false;
        }
        
        parent::expectOneRecord($count);
    }
    
    public function expectModify($count_modify_row){
        return false;
    }
}

?>