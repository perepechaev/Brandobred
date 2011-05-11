<?php
class NextPrevPager extends Pager
{
    /**
     * Текущая страница
     *
     * @var int
     */
    public $page;

    /**
     * Общее количество элементов
     *
     * @var int
     */
    public $count;
    
    /**
     * Элементов на странице
     *
     * @var int
     */
    public $countPage;
    

    /**
     * @param int $page - текущая страница
     * @param int $count - общее количество элементов списка
     * @param int $countPage - количество элементов на странице
     */
    private function __construct($page, $count, $countPage){
        $this->page      = $page;
        $this->count     = $count;
        $this->countPage = $countPage;
        
        if ($page && $count && $countPage) $this->init();
    }
    
    public function init(){
        $this->setCountPage( ceil($this->count / $this->countPage) );
        $this->setCurrentPage( $this->page );
        $this->setStrategy( new PagerNextPrevStrategy() );
        $this->setSeparatorTemplate(array($this,'emptySeparator'));
    }
    
    public function emptySeparator(){
        return "&nbsp;";
    }
    
    static public function create($page = null, $count = null, $countPage = 10){
            return new self($page, $count, $countPage);
    }
    
}

class PagerNextPrevStrategy extends PagerStrategy 
{
    public function complete($currentPage, $countPage, $item, $cur){
        $empty = array($this, 'itemEmpty');
        
        if ($countPage == 0) return array();

        $a = array( 
            max($currentPage - 1, 1) => array($this, 'itemPrev'),
            $currentPage => $empty, 
            min($currentPage + 1, $countPage) => array($this, 'itemNext'),
        );
        
        if ($currentPage == $countPage) {
            unset($a[$countPage]);
        }
        
        return $a;
    }
    
    public function itemEmpty($page, $link){
        return "";
    }
    public function itemPrev($page, $link){
        return "<a href=\"$link\">&larr;&nbsp;назад</a>";
    }
    public function itemNext($page, $link){
        return "<a href=\"$link\">вперед&nbsp;&rarr;</a>";
    }
}

?>