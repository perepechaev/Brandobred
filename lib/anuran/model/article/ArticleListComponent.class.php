<?php
// XXX: Разбить на два объекта: ArticleListComponent и ArticleAccessorComponent


class ArticleListComponent implements IteratorAggregate
{
    private $items      = array();
    private $count      = 0;

    public function count(){
        return $this->count;
    }

    public function add(ArticleDataComponent $object){
        $this->items[]  = $object;
        $this->count++;
    }

    /**
     * @return MysqlIterator
     */
    public function getIterator() {
        return new MysqlIterator($this->items);
    }
}