<?php

require_once(dirname(__FILE__) . '/../TestHead.php');
require_once(PATH_CORE . '/Mysql.class.php');
require_once(dirname(__FILE__) . '/TestMysqlData.class.php');
require_once(dirname(__FILE__) . '/TestMysqlCriteria.class.php');
require_once(PATH_MODEL . '/billing/BillingImport.class.php');


class TestMysql extends Test
{
    public function test_constructor(){
        $default_id = 'default';
        $mysql      = Mysql::instance();
        equal($mysql === Mysql::instance());
        equal($mysql !== $new = Mysql::instance('new'));
        equal($mysql === Mysql::instance());
        equal($new === Mysql::instance('new'));
        equal($new !== $mysql);

        $this->result('Test Mysql constructor', 'ok');
    }

    public function test_existsDatabase(){
        $mysql      = Mysql::instance();

        $mysql->createDbIfNotExists();
        $mysql->useDb();

        $this->result('Test exists database', 'ok');
    }

    public function test_createTable(){
        $mysql      = Mysql::instance();
        $biling     = new Billing();

        $mysql->setTablePrefix("test__");
        $mysql->createTable($biling);
        equal( $mysql->isTableExists($biling) === 1 , 'Таблица не cоздана?');
        $this->result('Test create table', "ok");
    }

    public function test_dropTable(){
        $mysql      = Mysql::instance();
        $billing    = new Billing();

        equal($mysql->isTableExists($billing) === 1, 'Для работы теста таблица должна существовать');

        $mysql->dropTable($billing);
        equal($mysql->isTableExists($billing) === 0, 'Ошибка удаления таблицы');
        $this->result('Test drop table', 'ok');
    }

    public function test_useTablePrefix(){
        $billing    = new Billing();

        $mysql      = Mysql::instance();
        $mysql->setTablePrefix('test__');
        $mysql->createTable($billing);
        equal($mysql->isTableExists($billing) === 1, __LINE__);

        $mysql->setTablePrefix('test___');
        equal($mysql->isTableExists($billing) === 0, __LINE__);

        try {
            $mysql->dropTable($billing);
        }
        catch (MysqlException $e){
            // Note: Mysql error = Unknown table `bla-bla`
            if ($e->getCode() !== MysqlException::UNKNOWN_TABLE ) throw $e;
        }
        equal($mysql->isTableExists($billing) === 0, __LINE__);

        $mysql->setTablePrefix('test__');
        equal($mysql->isTableExists($billing) === 1, __LINE__);

        $this->result('Test prefix table', 'ok');
    }

    public function test_billingRequestField(){
        $b      = new Billing();
        $mysql  = Mysql::instance();
        try {
            $mysql->select("", $b);
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::NOT_NULL_REQUEST){
                $this->result("Wrong Exception code({$e->getCode()})", $e->getMessage());
            }
        }

        $b->service_id  = 1;
        $b->abonent_id  = 100;
        $b->date        = '2006-02-04';
        $b->amount      = '0.00';
        $mysql->select("", $b);

        $this->result('Test request param', 'ok');
    }

    public function test_mysqlSelectQuery(){
        $b      = new Billing();
        $b->service_id = 3;
        $b->abonent_id = 4;
        $b->date       = '2009';
        equal($b->structure('getSqlFields', 'test__') === "`test__billing`.`service_id`, `test__billing`.`abonent_id`, `test__billing`.`date`, `test__billing`.`amount`", __LINE__ . " Неправильное построение полей.");

        $mysql  = Mysql::instance();
        $mysql->select("", $b);
        equal($mysql->last() === "SELECT `test__billing`.`service_id`, `test__billing`.`abonent_id`, `test__billing`.`date`, `test__billing`.`amount` FROM `test__billing` ", __LINE__);

        try {
            $b->service_id = 643;
            $b->abonent_id = 13;
            $mysql->select('WHERE `service_id` = :service_id:', $b);
            equal($mysql->last() === "SELECT `test__billing`.`service_id`, `test__billing`.`abonent_id`, `test__billing`.`date`, `test__billing`.`amount` FROM `test__billing` WHERE `service_id` = 643", __LINE__."не совпадают строки");
        }
        catch (MysqlException $e){
            $this->result('Unknow code exception ('. $e->getCode() . ')', __LINE__ . " ". $e->getMessage());
        }

        try {
            $mysql->select('WHERE `amount` > :amount:', $b);
        }
        catch (MysqlException $e){
            $this->result('запрос: ', $mysql->last());
            $this->error($e, __LINE__);
        }

        $this->result('Test SqlFields', 'ok');
    }
    
    public function test_NullDateType(){
        $data    = new TestMysqlData();
        Mysql::instance()->createTable($data);

        $data->service_id    = 1;
        $data->abonent_id	 = 2;
        $data->date          = date('Y-m-d');
        $data->amount	     = 0.1;
        $data->comment       = "some comment";
        $data->title         = $title = 'some title ' . rand(1, 10000);
        $data->dateNull      = null;
        $data->save();
        
        $data                = new TestMysqlData();
        $data->title         = $title;
        Mysql::instance()->get($data, '', 'WHERE `title`=:title:');
        equal(is_null($data->dateNull), 'Дата сохранилась не как NULL: ' . var_export($data->dateNull, true));
        
        Mysql::instance()->dropTable($data);
        $this->result('Null date', 'ok');
    }

    public function test_wrongParam(){
        try {
            $b              = new Billing();
            $b->service_id  = 'fuck';
            Mysql::instance()->select('', $b);
            $this->detail(true);
            $this->result("Not exception for wrong value", 'ERROR (' .__LINE__ .' line)' );
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::ERROR_FIELD_VALUE){
                $this->error($e, __LINE__);
            }
        }

        $this->result('Test wrong params', 'ok');
    }

    public function test_insertQuery(){
        $mysql      = Mysql::instance();
        $billing    = new Billing();

        try {
            $mysql->insert($billing);
            $this->error(new Exception('И как он вставил запись? Там же request-ы одни'), __LINE__);
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::NOT_NULL_REQUEST) throw $e;
        }

        $mysql->select("", $billing);
        equal($mysql->fetch($billing, $list)->count() === 0, __LINE__ . " Получили: ".print_r($list, true));

        $billing->service_id    = 100;
        $billing->abonent_id    = 200;
        try {
            $mysql->insert($billing);
            equal(false, __LINE__);
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::NOT_NULL_REQUEST ) throw $e;
        }

        $billing->date          = '2006-02-28';

        try {
            $mysql->insert($billing);
            equal(false, __LINE__);
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::NOT_NULL_REQUEST ) throw $e;
        }

        $billing->amount    = 100;
        $mysql->setTablePrefix('s_');
        $mysql->createTable($billing);
        $mysql->insert($billing);

        unset($billing);
        $billing    = new Billing();
        equal(is_null($billing->service_id), __LINE__);
        equal(is_null($billing->abonent_id), __LINE__);
        equal(is_null($billing->date), __LINE__);
        equal(is_null($billing->amount), __LINE__);

        $mysql->select('', $billing);
        $mysql->fetch($billing, $list);
        equal(is_null($billing->service_id), __LINE__);
        equal(is_null($billing->abonent_id), __LINE__);
        equal(is_null($billing->date), __LINE__);
        equal(is_null($billing->amount), __LINE__);
        equal($list->count() === 1, __LINE__ . " \$list->count()=".var_export($list->count(), true));
        foreach ($list as $bil){
            equal($bil->service_id == 100, __LINE__ . " \$bil->service_id=".var_export($bil->service_id, true));
            equal($bil->abonent_id == 200, __LINE__);
            equal($bil->date === '2006-02-28', __LINE__);
            equal($bil->amount == 100, __LINE__);
        }


        $mysql->dropTable($billing);
        $mysql->setTablePrefix('');;

        $this->result('Test insert query', 'ok');
    }

    public function test_identifyMysql(){
        $mysql      = Mysql::instance();
        $billing    = new Billing();

        try {
            $mysql->dropTable($billing);
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::UNKNOWN_TABLE ) throw $e;
        }

        $mysql_2    = Mysql::instance('id2');
        $mysql_2->setTablePrefix('id2');

        $mysql_2->createTable($billing);

        equal($mysql->isTableExists($billing) === 0, __LINE__);
        equal(Mysql::instance('id2')->isTableExists($billing) === 1, __LINE__);
        equal(Mysql::instance('id3')->isTableExists($billing) === 0, __LINE__);

        Mysql::instance('id3')->setTablePrefix('id3');
        equal(Mysql::instance('id2')->isTableExists($billing) === 1, __LINE__);
        equal(Mysql::instance('id3')->isTableExists($billing) === 0, __LINE__);

        Mysql::instance('id3')->setTablePrefix('id2');
        equal(Mysql::instance('id2')->isTableExists($billing) === 1, __LINE__);
        equal(Mysql::instance('id3')->isTableExists($billing) === 1, __LINE__);

        $mysql_2->dropTable($billing);
        $this->result('Test identify Mysql object', 'ok');
    }

    public function test_sqlInject(){
        $mysql      = Mysql::instance();
        $mysql->setTablePrefix('test__');
        $s          = new ServiceGroup();
        $mysql->createTable($s);
        $s->name    = 'test field';
        $mysql->select("WHERE `name` = :name:", $s);
        equal($mysql->last() === "SELECT `test__service_group`.`id`, `test__service_group`.`name` FROM `test__service_group` WHERE `name` = 'test field'", __LINE__);

        $s->name    = "test field's";
        $mysql->select("WHERE `name` = :name:", $s);
        equal($mysql->last() === 'SELECT `test__service_group`.`id`, `test__service_group`.`name` FROM `test__service_group` WHERE `name` = \'test field\\\'s\'', __LINE__);

        $mysql->dropTable($s);
        $mysql->setTablePrefix('');

        $this->result('Test sql injection', 'ok');
    }

    public function test_get(){
//        $this->detail(true);
        $mysql                  = Mysql::instance();
        $billing                = new Billing();

        $mysql->createTable($billing);

        $billing->service_id    = 1;
        $billing->abonent_id    = 101;
        $billing->date          = '2006-10-20';
        $billing->amount        = 0.02;

        $mysql->insert($billing);
        $mysql->insert($billing);
        $mysql->insert($billing);

        $mysql->select('', $billing);
        $mysql->fetch($billing, $list);
        $count      = $list->count();

        $mysql->get($billing, 'count(*) as count');
        equal((string) $billing->count == $count, '$billing->count=' . $billing->count . '!= $count=' . $count . '(' . __LINE__ . ')');
        
        $this->detail(true);
        $billing->date          = '2009-02-28';
        $billing->where_date    = "2006-10-20";
        $mysql->get($billing, '', 'WHERE `date` = :where_date: LIMIT 1');
        //dump($mysql->last());
        equal($billing->date === '2006-10-20', "Дата должна быть 2006-10-20, у нас же: " . var_export($billing->date, true));

        $mysql->dropTable($billing);
        $this->result('Test $mysql->get()', 'ok');
    }
    
    public function test_set(){
        $mysql      = Mysql::instance();
        
        $data       = new TestMysqlDataDelete();
        $data->post = '1';
        equal($data->post === 'Realy set post: 1', var_export($data->post, true));

        $data->post = 'foo';
        equal($data->post === 'Realy set post: foo');

        $mysql->createTable($data);
        assert($mysql->isTableExists($data));
        
        $data->service_id    = 1;
        $data->abonent_id    = 1;
        $data->date          = date('Y-m-d');
        $data->amount        = 10.55;
        $data->comment       = "This's comment";
        $data->title         = "This's title";
        $data->post          = 'foo';
        $data->save();
        
        $data_id    = $data->id;
        $data       = new TestMysqlDataDelete();
        $data->id   = $data_id;
        $mysql->get($data, '', 'WHERE `id`=:id: LIMIT 1'); 
        equal($data->post === 'Realy set post: foo', 'Realy set post: foo !== ' . var_export($data->post, true));
        
        $mysql->dropTable($data);
        
        $this->result('Test $mysql->__set()', 'ok');
    }

    

    /**
     * Проверка работы с MysqlData:
     *
     */
    public function test_save(){
        $mysql                  = Mysql::instance();
        $newData                = new TestMysqlData();
        $mysql->createTable($newData);
        
        $s_id                   = 9;
        $a_id                   = 999;
        $date                   = '2009-02-28';  
        $amount                 = 15.2;
        $comment                = 'Мой комментарий на создание записи в табличке';
        $title                  = 'Мой тайтл созданный для теста.';
        
        $newData->service_id    = $s_id;
        $newData->abonent_id    = $a_id;
        $newData->date          = $date;
        $newData->amount        = $amount;
        $newData->comment       = $comment;
        $newData->title         = $title;
    
        
        // Проверка возможно ли сохранение в приципе.
        $newData->save();
        
        // Проверка корректности сохраненных данных.
        $newData->amount = 'fuck';
        $mysql->get($newData, 'comment', 'WHERE `title`=:title: LIMIT 1');
        equal($newData->comment === $comment, "Данные сохранились некорректно в БД:" . var_export($newData->comment, true) );

        // Проверка ситуации, когда поля заданы некорректно.
        $newData->date          = 'Всякая бяка,но не дата';
        try{
            $newData->save();    
        }
        catch (MysqlException $e){
            equal( $e->getCode() === MysqlException::ERROR_FIELD_VALUE , "Записались данные некорректных типов в таблицу.");
        }
        
        $this->result('Test $mysql->save(MysqlData)', 'ok');
    }
    
    /**
     * Проверка работы с MysqlList
     *
     */
    public function test_save_list(){
        $mysql          = Mysql::instance();
        $newDataForList = new TestMysqlData();
        $mysql->dropTable($newDataForList);
        $mysql->createTable($newDataForList);
        $list           = new MysqlList();
        
        $s_id           = 9;
        $a_id           = 999;
        $date           = '2009-02-28';  
        $amount         = 15.2;
        $comment        = 'Мой комментарий на создание записи в табличке для сохранения листа.';
        $title          = 'Мой тайтл созданный для теста для сохранения листа.';
        
        
        for ($i = 0; $i < 20; $i++){
            $newDataForList                = new TestMysqlData();
            $newDataForList->service_id    = $s_id . $i;
            $newDataForList->abonent_id    = $a_id . rand(20, 40);
            $newDataForList->date          = date('Y-m-d', mktime(null, null, null, rand(1, 12), rand(1,28), rand(1990, 2030)));
            $newDataForList->amount        = $amount . rand(40, 60);
            $newDataForList->comment       = $comment . rand(60, 80);
            $newDataForList->title         = $title . $i;
            
            $list->add($newDataForList);
        }
        
        // Проверка возможно ли сохранение в приципе.
        $list->save();
        $data_count = clone $newDataForList;
        $mysql->get($data_count, 'count(*) as count');
        equal(20 == $data_count->count, "20 !== " . $data_count->count);
        
        // Проверка сохраненных данных
        $data           = new TestMysqlData();
        $data->title    = $title . '5'; 
        $mysql->get($data, '', 'WHERE title = :title:');
        equal($data->service_id ===  intval($s_id . '5'));
        
        // Проверка корректности сохраненных данных.
        $newDataForList                = new TestMysqlData();
        $newDataForList->service_id    = 1;
        $newDataForList->abonent_id    = 2;
        $newDataForList->date          = '2000-01-01';
        $newDataForList->amount        = 1.1;
        $comment                       = 'Проверка записи данных листом - комментарий';
        $title                         = 'Проверка записи данных листом - заголовок';
        $newDataForList->comment       = $comment;
        $newDataForList->title         = $title;
                    
        $list->add($newDataForList);
        $list->save();            
        
        $mysql->get($newDataForList, 'comment', 'WHERE `title`=:title: LIMIT 1');
        equal($newDataForList->comment === $comment, "Данные сохранились некорректно в БД:" . var_export($newDataForList->comment, true) );
        
        // Проверка ситуации, когда поля заданы некорректно.
        $list                          = new MysqlList();
        
        $comment_1            = 'Данные корректны, но не должны попасть в БД благодаря отмене транзакции. Запись перед трэшем';
        $data1                = clone $newDataForList;
        $data1->date          = '2009-03-03';
        $data1->comment_1     = $comment_1;
        $data1->comment       = $comment;
        $data1->title         = $title;
        $list->add($data1);
        
        $comment_2            = 'Данные не корректны';
        $data2                = clone $newDataForList;
        $data2->date          = 'Всякая бяка,но не дата для листа';
        $data2->comment_2     = $comment_2;
        $data2->comment       = $comment;
        $data2->title         = $title;
        $list->add($data2);
        
        $comment_3            = 'Данные корректны, но не должны попасть в БД благодаря отмене транзакции. Запись после трэша';
        $data3                = clone $newDataForList;
        $data3->date          = '2009-03-03';
        $data3->comment_3     = $comment_3;
        $data3->comment       = $comment;
        $data3->title         = $title;
        $list->add($data3);
        
        try{
            $list->save();    
            equal(false, "Ожидается исключительное событие MysqlException::ERROR_FIELD_VALUE");
        }
        catch (MysqlException $e){
            if ( $e->getCode() !== MysqlException::ERROR_FIELD_VALUE) throw $e;
        }
        
        // Проверка транзакции. (Отменилась ли вся транзакция или некоторые данные попали в БД)
        try{
            $mysql->get($data1, 'title', 'WHERE `comment`=:comment_1: LIMIT 1');
            equal(false, "Ожидается исключительное событие MysqlException::EXPECT_ONE_RECORD");
        }
        catch (MysqlException  $e){
            if ($e->getCode() !== MysqlException::EXPECT_ONE_RECORD) throw $e;
        }
        
        try{
            $mysql->get($data3, '', 'WHERE `comment`=:comment_3: LIMIT 1');
            equal(false, "Ожидается исключительное событие MysqlException::EXPECT_ONE_RECORD");
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::EXPECT_ONE_RECORD) throw $e;
        }
        
        $this->result('Test $mysql->save(MysqlList)', 'ok');
    }
    
    public function test_delete(){
        
        $mysql                  = Mysql::instance();
        $newData                = new TestMysqlDataDelete();
        $mysql->createTable($newData);
        $mysql->dropTable($newData);
        $mysql->createTable($newData);
        
        $s_id                   = 91;
        $a_id                   = 9991;
        $date                   = '2007-12-28';  
        $amount                 = 15.23;
        $comment                = 'Тест удалялки коммент.';
        $title                  = 'Тест удялялки тайтл.';
        
        $newData->service_id    = $s_id;
        $newData->abonent_id    = $a_id;
        $newData->date          = $date;
        $newData->amount        = $amount;
        $newData->comment       = $comment;
        $newData->title         = $title;
        $newData->save();

        
        $mysql->delete($newData);
        try{
            $mysql->get($newData, '', 'WHERE `comment`=:comment: LIMIT 1');
            equal(false, "Ожидается исключительное событие MysqlException::EXPECT_ONE_RECORD (удаление прошло криво)");
        }
        catch (MysqlException  $e){
            if ($e->getCode() !== MysqlException::EXPECT_ONE_RECORD) throw $e;
        }

        equal( $newData->comment !== '', 'Delete не очистил DataComponent');
        
        $this->result('Test $mysql->delete()', 'ok');
    }
    
    public function test_clean(){
        
        $mysql                  = Mysql::instance();
        $newData                = new TestMysqlDataDelete();
        $mysql->createTable($newData);
        
        $s_id                   = 91;
        $a_id                   = 9991;
        $date                   = '2007-12-28';  
        $amount                 = 15.23;
        $comment                = 'Тест удалялки коммент.';
        $title                  = 'Тест удялялки тайтл.';
        
        $newData->service_id    = $s_id;
        $newData->abonent_id    = $a_id;
        $newData->date          = $date;
        $newData->amount        = $amount;
        $newData->comment       = $comment;
        $newData->title         = $title;
        
        $newData->someTitle     = 'Пустышка';

        $newData->clean();
        
        equal( $newData->comment !== '', 'Clean не очистил MysqlData объект');
        
        try {
            $newData->someTitle;
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::NOT_FIND_FIELD_IN_STRUCTURE) throw $e;
        }
        
        $this->result("Test clean", 'ok');
    }
    
    public function test_isModify(){
        $mysql          = Mysql::instance();
        $data           = new TestMysqlData();
        equal($data->isModify() === false);
        
        $data->amount   = '10.2';
        equal($data->isModify() === true);
        
        $data->clean();
        equal($data->isModify() === false);
        
        $data->date          = '2000-01-01';
        $data->amount        = '10.2';
        $data->title         = 'Title...';
        $data->comment       = 'Comment';
        $data->service_id    = 2;
        $data->abonent_id    = 1;
        equal($data->isModify() === true);
        
        $data->save();
        equal($data->isModify() === false);
    }
    
    public function test_prepare(){
        
        $mysql                  = Mysql::instance();
        $newData                = new TestMysqlData();
        $newData->date          = '2000-01-01';
        $newData->amount        = '10.2';
        $newData->title         = 'Title...';
        $newData->comment       = 'Тест припара соммент';
        $newData->service_id    = 1;
        $newData->abonent_id    = 1;
        
        equal( is_null($newData->tested));
        $newData->save();
        equal( $newData->tested === 'prepare is execute');
        
        $this->result("Test prepare", 'ok');
    }
    
    public function test_alterTable(){
        $mysql  = Mysql::instance();

        $data   = new TestMysqlDataAlterTable();
        $sql    = $data->getSqlConstructor()->getSqlAlterField('post');
        //                  ALTER TABLE `test_mysql_data` CHANGE COLUMN `post` `post` int(11) NOT NULL
        equal(preg_match('/^ALTER TABLE `test_mysql_data` CHANGE COLUMN `post` `post` int\(11\) NOT NULL\s*$/', $sql));
        $mysql->query('UPDATE `test_mysql_data` SET `post` = "0"');
        $mysql->query( $sql );
        
        $mysql->alterTable($data);
        
        $mysql->query("SELECT `alter` FROM " . $data->getSqlConstructor()->getSqlTableName());
        
        $this->result('Alter table', 'ok');
    }
    
    public function test_transaction(){
        $mysql  = Mysql::instance();
        
        $title              = 'Титл контроля';
        $data   = new TestMysqlData();
        $data->service_id   = 9000;
        $data->abonent_id   = 1;
        $data->date         = date('Y-m-d');
        $data->amount       = 1;
        $data->comment      = 'Контрольный объект';
        $data->title        = $title;
        $data->post         = 'post';
        $data->dateNull     = NULL;
        
        $doTransaction      = true;
        
        $mysql->alterTable($data);
        
        $data1  = clone $data;
        // Нормальный вариант - 1 транзакция
        
        try{
            
            $mysql->start_transaction();
            $data1->insert();
            if ($doTransaction === false){
                $mysql->revert_transaction();
            }
            else {
                $mysql->commit_transaction();
            }
        }
        catch (Exception $e){
            dump($e->getMessage());
        }
        
        $criteria   = new TestMysqlCriteriaConcreteEqual();
        $criteria->setData($data1);
        $criteria->setId(9000);
        $dataAfter  = $mysql->getByCriteria($criteria);
        equal($title === $dataAfter->title , 'Криво прошла транзация. Должна пройти нормально');
        
        // Проверка метода isModify()
        equal($dataAfter->isModify() === false);
        
        // Вариант проваленой транзакции. 1 - транзакция
        $doTransaction  = false;
        $data2              = clone $data;
        $data2->service_id  = 9001;
        try{
            $mysql->start_transaction();
            $data2->insert();
            if ($doTransaction === false){
                $mysql->revert_transaction();
            }
            else {
                $mysql->commit_transaction();
                equal(false);
            }
        }
        catch (Exception $e){
            dump($e->getMessage());
        }
        
        $criteria   = new TestMysqlCriteriaConcreteEqual();
        $criteria->setData($data2);
        $criteria->setId(9001);
        try{
            $dataAfter  = $mysql->getByCriteria($criteria);
            equal(false);
        }
        catch (MysqlException $e){
            equal(MysqlException::EXPECT_ONE_RECORD === $e->getCode() , 'Транзакция произвела запись в БД, а не должна бы ');            
        }
        
        // Нормальный вариант. 2 - транзакции
        $doTransaction      = true;
        $data1              = clone $data;
        $data2              = clone $data;
        
        $data1->service_id  = 9002;
        $data2->service_id  = 9003;
        
            $mysql->start_transaction();
               $data1->insert();
                    $mysql->start_transaction();
                    $data2->insert();
                    $mysql->commit_transaction();
            $mysql->commit_transaction();
        // Проверим что получилось. Должны записаться оба объекта в БД.
        $criteria   = new TestMysqlCriteriaConcreteEqual();
        $criteria->setData($data1);
        $criteria->setId(9002);
        $dataAfter1  = $mysql->getByCriteria($criteria);
                    
        $criteria   = new TestMysqlCriteriaConcreteEqual();
        $criteria->setData($data2);
        $criteria->setId(9003);
        $dataAfter2  = $mysql->getByCriteria($criteria);

        equal($dataAfter1->title === $dataAfter2->title, 'неверно записались данные в БД после транзакции');
        
        // Вариант с проваленной вложенной транзакцией (провалена внутренняя).
        $doTransaction      = true;
        $data1              = clone $data;
        $data2              = clone $data;
        
        $data1->service_id  = 9004;
        $data2->service_id  = 9005;
        
        try{
            $mysql->start_transaction();
            $data1->insert();
            try{
                $mysql->start_transaction();
                $data2->insert();
                equal(false);
                // До сюда не доходим
                $mysql->commit_transaction();
            }
            catch (Exception $e){
                $mysql->revert_transaction(); // Создается MysqlException::TRANSACTION_NESTED_REVERT
            }
            // До сюда не доходим
            equal(false);
            $mysql->commit_transaction();
        }
        catch (MysqlException $e){
            if (MysqlException::TRANSACTION_NESTED_REVERT !== $e->getCode()) throw $e;
            $mysql->revert_transaction();
        }
        
        // Проверим что получилось. Не должно быть ни одной записи в БД.
        $criteria   = new TestMysqlCriteriaConcreteEqual();
        $criteria->setData($data1);
        $criteria->setId(9004);
        try{
            $dataAfter1  = $mysql->getByCriteria($criteria);
            equal(false, "Состоялась запись в БД, а не должна бы. Транзакция внешняя");
        }
        catch (MysqlException $e){
            equal(MysqlException::EXPECT_ONE_RECORD === $e->getCode(), "Состоялась запись в БД, а не должна бы. Транзакция внешняя");                    
        }
        
        
        $criteria   = new TestMysqlCriteriaConcreteEqual();
        $criteria->setData($data2);
        $criteria->setId(9005);
        try{
            $dataAfter2  = $mysql->getByCriteria($criteria);
            equal(false , "Состоялась запись в БД, а не должна бы. Транзакция внешняя");
        }
        catch (MysqlException $e){
            equal(MysqlException::EXPECT_ONE_RECORD === $e->getCode(), "Состоялась запись в БД, а не должна бы. Транзакция внутренняя");                    
        }
        
        // Вариант с проваленной вложенной транзакцией (провалена внешняя).
        $data1              = clone $data;
        $data2              = clone $data;
        
        $data1->service_id  = 9006;
        $data2->service_id  = 9007;
        
            $mysql->start_transaction();
                $data1->insert();
                $mysql->start_transaction();
                    $data2->insert();
                $mysql->commit_transaction();
            $mysql->revert_transaction();
            
//        dump($this->getCountByServiceId($data1), true);
        
        // Проверим что получилось. Не должно быть ни одной записи в БД.
        $criteria   = new TestMysqlCriteriaConcreteEqual();
        $criteria->setData($data1);
        $criteria->setId(9006);
        try{
            $dataAfter1  = $mysql->getByCriteria($criteria);
            equal(false, "Состоялась запись в БД, а не должна бы. Транзакция внешняя");
        }
        catch (MysqlException $e){
            equal(MysqlException::EXPECT_ONE_RECORD === $e->getCode(), "Состоялась запись в БД, а не должна бы. Транзакция внешняя");                    
        }
        
        
        $criteria   = new TestMysqlCriteriaConcreteEqual();
        $criteria->setData($data2);
        $criteria->setId(9007);
        try{
            $dataAfter2  = $mysql->getByCriteria($criteria);
            equal(false , "Состоялась запись в БД, а не должна бы. Транзакция внешняя");
        }
        catch (MysqlException $e){
            equal(MysqlException::EXPECT_ONE_RECORD === $e->getCode(), "Состоялась запись в БД, а не должна бы. Транзакция внутренняя");                    
        }
        
        // Тупые ошибки:
        
        try{
            $mysql->commit_transaction();
            equal(false);
        }
        catch (MysqlException $e){
            equal(MysqlException::TRANSACTION_ERROR_COUNT === $e->getCode(), "Случился комит без старта транзакции");
        }
        
        try{
            $mysql->revert_transaction();
            equal(false);
        }
        catch (MysqlException $e){
            equal(MysqlException::TRANSACTION_ERROR_COUNT === $e->getCode(), "Случился реверт без старта транзакции");
        }
        //XXX: обрулить в будующем
//        
//        // Вариант с двумя вложенными транзакциями (транзакции параллельны)
//        
//        //Провалена первая из параллельных ($data2)
//        $doTransaction      = true;
//        $data1              = clone $data;
//        $data2              = clone $data;
//        $data3              = clone $data;
//        
//        $data1->service_id  = 9008;
//        $data2->service_id  = 9009;
//        $data3->service_id  = 9010;
//        
//        // count = 0 (true)
//        $mysql->start_transaction();//+1 = 2
//        $data1->insert();
//            $mysql->start_transaction();//+1 = 3
//            $data2->insert();
//            $mysql->revert_transaction();//0 = 0
//            
//            $mysql->start_transaction();//+1 = 1
//            $data3->insert();
//            $mysql->commit_transaction();//-1 = 1
//        $mysql->commit_transaction();//-1 = 0
//        
//        // Проверяем что получилось
//        $criteria   = new TestMysqlCriteriaConcreteEqual();
//        $criteria->setData($data1);
//        $criteria->setId(9008);
//        try{
//            $mysql->getByCriteria($criteria);
//            equal(false, 'прошла запись в БД с кривой транзакцией (внешняя транзакция $data1)');
//        }
//        catch (MysqlException $e){
//            equal(MysqlException::EXPECT_ONE_RECORD === $e->getCode(), 'прошла запись в БД с кривой транзакцией');
//        }
//        
//        $criteria   = new TestMysqlCriteriaConcreteEqual();
//        $criteria->setData($data2);
//        $criteria->setId(9009);
//        try{
//            $mysql->getByCriteria($criteria);
//            equal(false, 'прошла запись в БД с кривой транзакцией (внутренняя первая транзакция $data2)');
//        }
//        catch (MysqlException $e){
//            equal(MysqlException::EXPECT_ONE_RECORD === $e->getCode(), 'прошла запись в БД с кривой транзакцией');
//        }
//        
//        $criteria   = new TestMysqlCriteriaConcreteEqual();
//        $criteria->setData($data3);
//        $criteria->setId(9010);
//        try{
//            $mysql->getByCriteria($criteria);
//            equal(false, 'прошла запись в БД с кривой транзакцией (внутренняя вторая транзакция $data3)');
//        }
//        catch (MysqlException $e){
//            equal(MysqlException::EXPECT_ONE_RECORD === $e->getCode(), 'прошла запись в БД с кривой транзакцией');
//        }
        
        $this->result('test Transaction' , 'ok');
    }
    
    public function test_aliasFieldData(){
        $data       = new TestMysqlDataAlias();
        $criteria   = new TestMysqlCriteriaConcreteEqual();
        
        $criteria->setData($data);
        
        Mysql::instance()->query("TRUNCATE " . $data->getSqlConstructor()->getSqlTableName());
        Mysql::instance()->alterTable($data);
        
        $data->question     = 'Mega title';
        $data->service_id   = 4;
        $data->amount       = 8;
        $data->abonent_id   = 2;
        $data->date         = date('Y-m-d');
        $data->comment      = 'comment';
        $data->post         = 'post';
        $data->save();
        
        $criteria->setId($data->service_id);
        
        $data   = Mysql::instance()->getByCriteria($criteria);
        equal($data->service_id === 4);
        equal($data->abonent_id === 2);
        equal($data->amount === '8.00');
        equal($data->date === date('Y-m-d'));
        equal($data->comment === 'comment');
        equal($data->post === 'post', var_export($data->post, true));
        equal($data->title === 'Mega title');
        equal($data->question === 'Mega title');
        
        $data->clean();
        $data->title    = 'title';
        equal($data->title === $data->question);
        
        $this->result('MysqlData alias field', 'ok');
    }
    
    public function __destruct() {
        Mysql::instance('test')->setTablePrefix('test__');       

        Mysql::instance('test')->createDbIfNotExists( new Billing() );
        Mysql::instance()->createDbIfNotExists( new TestMysqlData() );
        Mysql::instance()->createDbIfNotExists( new TestMysqlDataDelete() );
        
        Mysql::instance('test')->dropTable( new Billing() );
        Mysql::instance()->dropTable( new TestMysqlData() );
        Mysql::instance()->dropTable( new TestMysqlDataDelete() );
        
    }
    
    final public function getCountByServiceId(MysqlData $data){
        $criteria   = new TestMysqlCriteriaConcreteEqual();
        $criteria->setData($data);
        $criteria->setId($data->service_id);
        
        $result     = new MysqlDataCount();
        Mysql::instance()->query($criteria->count());
        Mysql::instance()->fetch($result, $list);
        
        return (int) $list->current()->count;
    }
}

$test   = new TestMysql();
$test->complete();

require_once dirname(__FILE__) . '/TestMysqlCriteria.class.php';
 
?>