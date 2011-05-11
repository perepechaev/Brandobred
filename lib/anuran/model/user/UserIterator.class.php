<?php

class UserIteratorException extends Exception {}

class UserIterator implements Iterator
{
    private $var = array();

    public function __construct($array) {
        if (is_array($array) ) {
            $this->var = $array;
        }
        else {
            throw new UserIteratorException('Ожидается массив, получен же: ' . var_export($array, true));
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
        next($this->var);
        return $this->current();
    }

    public function valid() {
        return $this->current() !== false;
    }

}

?>