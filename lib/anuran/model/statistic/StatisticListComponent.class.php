<?php

class StatisticListComponent extends ObjectListComponent implements IteratorAggregate
{
    /**
     * @return MysqlIterator
     */
    public function getIterator() {
        return new MysqlIterator($this->items);
    }

    public function getDaysIterator($timestamp, $count){

        $items      = array();
        for ($i = 1; $i <= $count; $i++){
            $current_date       = date('Y-m-d', $timestamp + 86400 * $i);
            $empty              = true;
            foreach ($this->items as $item){
                if ($item->date == $current_date){
                    $items[]        = $item;
                    $empty          = false;
                    break;
                }
            }
            if ($empty){
                $empty              = clone $item;
                $empty->general_num = 0;
                $empty->unique_num  = 0;
                $empty->date        = $current_date;
                $items[]            = $empty;
            }
        }

        return new MysqlIterator($items);
    }

    public function getMonthIterator($timestamp, $count){
        $items      = array();
        $year       = date('Y', $timestamp);
        $month      = date('m', $timestamp);

        for ($i = 1; $i <= $count; $i++){
            $current_time       = mktime(null, null, null, $month + $i, 1, $year);
            $current_date       = date('Y-m-d', $current_time);
            $empty              = true;
            foreach ($this->items as $item){
                if ($item->date == $current_date){
                    $items[]        = $item;
                    $empty          = false;
                    break;
                }
            }
            if ($empty){
                $empty              = clone $item;
                $empty->general_num = 0;
                $empty->unique_num  = 0;
                $empty->date        = $current_date;
                $items[]            = $empty;
            }
        }

        return new MysqlIterator($items);
    }
}