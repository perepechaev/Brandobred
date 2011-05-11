<?php

class StandartPager extends Pager
{
    public $page = 1;
    public $count;
    public $countPage = 10;
    
    public function __construct($page, $countPage = 10){
        $this->page      = $page > 0 ? $page : 1;
        $this->countPage = $countPage;
        $this->setStrategy( new StandartPagerStrategy() );
    }
    
    public function init(){
        $this->setCountPage( ceil($this->count / $this->countPage) );
        $this->setCurrentPage( $this->page );
        $this->setSeparatorTemplate(array($this,'emptySeparator'));
    }
    public function emptySeparator(){
        return "&nbsp;";
    }
    
}

class StandartPagerStrategy extends PagerStrategy
{
    public function complete($currentPage, $countPage, $item, $cur){
        if ($countPage <= 1 ){
            return array();
        }

        $pages  = array_fill(max(min($countPage-7, $currentPage - 3), 1), min($countPage, 8 ), true );
        if (empty($pages[1])){
            array_slice($pages, 1);
            $pages[1] = true;
        }
        ksort($pages);
        if (empty($pages[$countPage])){
            array_pop($pages);
            $pages[$countPage] = true;
        }
        foreach ($pages as $page => &$val){
            $val    = ($currentPage == $page) ? $cur : $item;
        }
        return $pages;
    }
}



?>