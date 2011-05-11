<?php

class StatisticDataComponent extends ObjectDataComponent
{
    protected function make(){
        $this->field('id',          'int',      array('request', 'unsigned', 'auto'));
        $this->field('ip',          'string',   array('request', 'length'=>15));
        $this->field('unique',      'string',   array('request', 'length'=>32));
        $this->field('get',         'string',   array('request'));
        $this->field('time',        'datetime', array('request'));

        $this->name('statistic');
    }

    /**
     * Получить статические данные по дням
     *
     * <code>
     *   SELECT count(id), COUNT(DISTINCT `unique`), date(`time`) as `time`
     *   FROM vulevu_statistic
     *   WHERE `time` >= '2008-06-04'
     *   GROUP BY date(`time`)
     * </code>
     *
     * @param   string  $date
     * @return  MysqlList
     */
    public function buildFromDate($date){
        $stat       = clone $this;
        $stat->time = $date;
        Mysql::instance()->select(
            "WHERE `time`>=:time: GROUP BY date(`time`) ORDER BY `date`",
            $stat,
            'count(`id`) general_num, COUNT(DISTINCT `unique`) unique_num, date(`time`) as `date`'
        );

        Mysql::instance()->fetch($stat, $result);
        return $result;
    }

    public function buildMonthFromDate($date){
        $stat       = clone $this;
        $stat->time = $date;
        Mysql::instance()->select(
            "WHERE `time`>=:time: GROUP BY month(`time`) ORDER BY `date`",
            $stat,
            'count(`id`) general_num, COUNT(DISTINCT `unique`) unique_num, DATE_FORMAT(DATE(`time`),"%Y-%m-01") as `date`'
        );

        Mysql::instance()->fetch($stat, $result);
        return $result;
    }
    
    public function __get($key){
        if (method_exists($this, 'get' . $key)){
            return $this->{'get' . $key}();
        }
        return parent::__get($key);
    }

    public function getDayWeek(){
        $day    = date('w', strtotime($this->date));
        $day    = $day == 0 ? 6 : --$day;
        return DateFormatted::getDayOfWeek($day);
    }

    public function getDayMonth(){
        return date('d.m', strtotime($this->date));
    }

    public function getMonth(){
        $month  = date('m', strtotime($this->date));
        return DateFormatted::getMonth($month, DateFormatted::MONTH_SHORT_NOM );
    }
}