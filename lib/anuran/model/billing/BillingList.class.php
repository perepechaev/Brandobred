<?php

require_once(PATH_MODEL . '/file/FileException.class.php');

/**
 * Класс загрузки Billing-объектов из файлов
 *
 * Вообще, это что-то очень страшное... просто
 * ума не приложу что будет, если с нашим
 * UserList что-то случится...
 *
 * NB: Отменяется, класс UserList заделан синглетоном.
 * Нам этого здесь не нужно. В будущем можно было бы
 * выделить общие участки и вынести в общедоступное
 * место. Сейчас этим занимать не будем, а сделаем
 * обыкновенный копипаст
 *
 */
class BillingList implements IteratorAggregate
{
    private $items      = array();
    private $count      = 0;
    private $callback;
    private $fileName;

    public function setFileName($filename) {
        $this->fileName = $filename;
    }

    public function count(){
        return $this->count;
    }

    public function add(IBilling $object){
        $this->items[]  = $object;
        $this->count++;
    }

    public function setCallback($callback){
        $this->callback = $callback;
    }

    public function loadFromFile(){
        FileException::isReadable($this->fileName);

        $this->items    = array();
        $handle         = fopen($this->fileName, 'r');

        // Пропускаем первую строчку
        fgetcsv($handle, 500, "\t");

        while (($data = fgetcsv($handle, 500, "\t")) !== false) {
            $this->add( call_user_func($this->callback, $data) );
        }
        fclose($handle);
    }

    /**
     * Получить список по дате
     *
     * Каждый объект Billing имеет следующие поля:
     * $billing->amount_sum  сумма платежей
     * $billing->service_name
     * $billing->group_name
     *
     * @param MysqlList $date
     */
    static public function listByDate($date){
        $mysql      = Mysql::instance();
        $billing    = new Service();
        $billing->date->addValue($date);
        $mysql->select( 'as t1 LEFT JOIN `billing` as t2 ON (t2.`service_id`=t1.`id`)'.
                        'LEFT JOIN `service_group` as t3 ON (t1.`group_id` = t3.id) '.
                        'WHERE `date`=:date: OR `date` IS NULL '.
                        'GROUP BY t1.id  ORDER BY service_id ASC',

                        $billing,
                        'CASE WHEN `date` IS NULL THEN 0.00 WHEN `date` IS NOT NULL THEN sum(amount) END as amount_sum, '.
                        't1.name as service_name, t3.name as group_name');
        $mysql->fetch(new Billing(), $list);
        return $list;
    }

    /**
     * Получить список по датам
     *
     * Каждый объект Billing имеет следующие поля:
     * $billing->amount_sum  сумма платежей
     * $billing->service_name
     * $billing->group_name
     *
     * @param MysqlList $date
     */
    static public function listByBetweenDate($date1, $date2){
        $mysql      = Mysql::instance();
        $date1      = mysql_real_escape_string($date1);
        $date2      = mysql_real_escape_string($date2);

        $mysql->query("
            SELECT g.name as group_name, s.name as service_name, (
                SELECT sum(amount)
                FROM billing
                WHERE (billing.service_id = s.id)
                AND (`date` BETWEEN '$date1' AND '$date2')
            ) as `amount_sum`
            FROM service s
            LEFT JOIN service_group g ON (s.group_id = g.id)
        ");

        return $mysql->fetch(new Billing());;
    }

    /**
     * Получить день в формате 2006-10-02
     * последней записи в Billing
     *
     * @return string
     */
    static public function getMaxDate(){
        $billing    = new Billing();
        Mysql::instance()->get($billing, 'max(`date`) as `date`');
        return $billing->date;
    }

    /**
     * @return MysqlIterator
     */
    public function getIterator() {
        return new MysqlIterator($this->items);
    }
}