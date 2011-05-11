<?php

class FileIteratorException extends Exception {}

class FileIterator implements Iterator
{
    private $var        = array();
    private $pageCount  = 1;

    public function __construct($array, $pageCount = 1) {
        $this->pageCount    = $pageCount;
        if (is_array($array) ) {
            $this->var = $array;
        }
        else {
            throw new FileIteratorException('Ожидается массив, получен же: ' . var_export($array, true));
        }
    }

    public function rewind() {
        reset($this->var);
    }

    public function current() {
        return current($this->var);
    }

    public function key() {
        return key($this->var);
    }

    public function next() {
        return next($this->var);
    }

    public function valid() {
        return $this->current() !== false;
    }

    /**
     * Получить количество страниц
     *
     * @return int
     */
    public function getCountPage(){
        return $this->pageCount;
    }

}

?>