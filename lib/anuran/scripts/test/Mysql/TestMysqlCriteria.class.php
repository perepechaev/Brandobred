<?php

require_once(dirname(__FILE__) . '/../TestHead.php');
require_once(PATH_CORE . '/Mysql.class.php');
require_once(dirname(__FILE__) . '/TestMysqlData.class.php');
require_once(dirname(__FILE__) . '/ConcreteCriteria.php');
require_once PATH_CORE . '/MysqlCriteria.class.php';

class TestMysqlCriteria extends Test
{
    protected $detail   = true;
    
    public function test_constructor(){
        $data           = new TestMysqlData();
        $constructor    = $data->getSqlConstructor();
        
        equal($constructor instanceof MysqlConstructor);
        
        $value  = $constructor->getSqlValue('service_id', 4);
        equal($value === 4);
        
        $value  = $constructor->getSqlValue('title', 'some title');
        equal($value === "'some title'");
        
        $value  = $constructor->getSqlValue('title', 'some \'title');
        equal($value === "'some \'title'", $value, true);
        
        try{
            $value  = $constructor->getSqlValue('service_id', "don't title");
            equal(false);
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::ERROR_FIELD_VALUE) throw $e;
        }
        
        $this->result('Mysql constructor', 'ok');
    }
    
    public function test_execute(){
        $criteria   = new TestMysqlCriteriaConcreteEqual();
        $data       = new TestMysqlData();
        
        // Right sql query
        $criteria->setData($data);
        $criteria->setId('1');
        
        $sql        = $criteria->execute();
        $rigthSql   = preg_match('/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`service_id` = 1$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // Double criteria execute
        $criteria->setData($data);
        $criteria->setId(1);
        
        $sql        = $criteria->execute();
        $rigthSql   = preg_match('/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`service_id` = 1$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // Without request field service_id
        $criteria   = new TestMysqlCriteriaConcreteEqual();
        $criteria->setData($data);
        try {
            $criteria->execute();
            equal(false);
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::NOT_NULL_REQUEST) throw $e;
        }
        
        // With many equals
        $criteria   = new TestMysqlCriteriaConcreteManyEqual();
        $criteria->setData( new TestMysqlData() );
        $criteria->setAbonentId(1);
        $criteria->setServiceId(8);
        $sql        = $criteria->execute();
        $rigthSql   = preg_match('/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`service_id` = 8 AND \2\.`abonent_id` = 1$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // Without data object
        $criteria   = new TestMysqlCriteriaConcreteManyEqual();
        $criteria->setAbonentId(1);
        $criteria->setServiceId(8);
        try{
            $criteria->execute();
            equal(false);
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::DATA_NOT_INSTANCE) throw $e;
        }
        
        $this->result('Criteria execute', 'ok');
    }
    
    public function test_condition(){
        // And condtion
        $criteria   = new TestMysqlCriteriaConcreteCondtitionAnd();
        $criteria->setData( new TestMysqlData() );
        $criteria->setAbonentId(4);
        $criteria->setServiceId(16);
        
        $sql        = $criteria->execute();
        $rigthSql   = preg_match('/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\(\2\.`service_id` = 16 AND \2\.`abonent_id` = 4\)$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // Or condition
        $criteria   = new TestMysqlCriteriaConcreteCondtitionOr();
        $criteria->setData( new TestMysqlData() );
        $criteria->setAbonentId(4);
        $criteria->setServiceId(16);
        
        $sql        = $criteria->execute();
        //SELECT * FROM `table` WHERE (`table`.`service_id` = 16 OR `table`.`abonent_id` = 4)
        $rigthSql   = preg_match('/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\(\2\.`service_id` = 16 OR \2\.`abonent_id` = 4\)$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // Multiple condition
        $criteria   = new TestMysqlCriteriaConcreteCondtitionMulltiple();
        $criteria->setData( new TestMysqlData() );
        $criteria->setAbonentId(4);
        $criteria->setServiceId(16);
        $criteria->setTitle('some title');
        
        $sql        = $criteria->execute();
        // SELECT * FROM `table` WHERE (`table`.`service_id` = 16 OR `table`.`abonent_id` = 4) AND `table`.`title` = 'some title'
        $rigthSql   = preg_match('/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\(\2\.`service_id` = 16 OR \2\.`abonent_id` = 4\) AND \2\.`title` = \'' . preg_quote('some title') . '\'$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // Multiple condition AND
        $criteria   = new TestMysqlCriteriaConcreteCondtitionMulltipleAnd();
        $criteria->setData( new TestMysqlData() );
        $criteria->setAbonentId(4);
        $criteria->setServiceId(16);
        $criteria->setTitle('some title');
        
        $sql        = $criteria->execute();
        // SELECT * FROM `table` WHERE `table`.`service_id` = 16 AND `table`.`abonent_id` = 4 AND `table`.`title` = 'some title'
        $rigthSql   = preg_match('/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`service_id` = 16 AND \2\.`abonent_id` = 4 AND \2\.`title` = \'' . preg_quote('some title') . '\'$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));

        // Multiple condition OR with one param
        $criteria   = new TestMysqlCriteriaConcreteCondtitionMulltipleOneOr();
        $criteria->setData( new TestMysqlData() );
        $criteria->setServiceId(16);
        $criteria->setTitle('some title');
        
        $sql        = $criteria->execute();
        // SELECT * FROM `table` WHERE `table`.`title` = 'some title' AND (`table`.`service_id` = 16)
        $rigthSql   = preg_match('/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`title` = \'' . preg_quote('some title') . '\' AND \(\2\.`service_id` = 16\)$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        $this->result('Mysql condition', 'ok');
    }
    
    public function test_conditionIsEqual(){
        // Multiple condition isEqual with all equal
        $criteria   = new TestMysqlCriteriaConcreteCondtitionIsEqual();
        $criteria->setData( new TestMysqlData() );
        $criteria->setServiceId(16);
        $criteria->setAbonentId(4);
        $criteria->setTitle('some title');

        $sql        = $criteria->execute();
        // SELECT * FROM `table` WHERE `table`.`title` = 'some title' AND `table`.`service_id` = 16 AND `table`.`abonent_id` = 4
        $rigthSql   = preg_match('/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`title` = \'' . preg_quote('some title') . '\' AND \2\.`service_id` = 16 AND \2\.`abonent_id` = 4$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // Multiple condition isEqual without service_id
        $criteria   = new TestMysqlCriteriaConcreteCondtitionIsEqual();
        $criteria->setData( new TestMysqlData() );
        $criteria->setAbonentId(4);
        $criteria->setTitle('some title');

        $sql        = $criteria->execute();
        // SELECT * FROM `table` WHERE `table`.`title` = 'some title' AND `table`.`abonent_id` = 4
        $rigthSql   = preg_match('/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`title` = \'' . preg_quote('some title') . '\' AND \2\.`abonent_id` = 4$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // Multiple condition isEqual with OR-Condition all equals
        $criteria   = new TestMysqlCriteriaConcreteConditionIsEqualInCondition();
        $criteria->setData( new TestMysqlData() );
        $criteria->setServiceId(12);
        $criteria->setAbonentId(4);
        $criteria->setTitle('some title');

        $sql        = $criteria->execute();
        // SELECT * FROM `table` WHERE (`table`.`title` = 'some title' OR `table`.`service_id` = 12 OR `table`.`abonent_id` = 4)
        $rigthSql   = preg_match('/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\(\2\.`title` = \'' . preg_quote('some title') . '\' OR \2\.`service_id` = 12 OR \2\.`abonent_id` = 4\)$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // Multiple condition isEqual with OR-Condition without title 
        $criteria   = new TestMysqlCriteriaConcreteConditionIsEqualInCondition();
        $criteria->setData( new TestMysqlData() );
        $criteria->setServiceId(12);
        $criteria->setAbonentId(4);

        $sql        = $criteria->execute();
        // SELECT * FROM `table` WHERE (`table`.`service_id` = 12 OR `table`.`abonent_id` = 4)
        $rigthSql   = preg_match('/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\(\2\.`service_id` = 12 OR \2\.`abonent_id` = 4\)$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // Multiple condition isEqual with OR-Condition without title, service_id 
        $criteria   = new TestMysqlCriteriaConcreteConditionIsEqualInCondition();
        $criteria->setData( new TestMysqlData() );
        $criteria->setAbonentId(8);

        $sql        = $criteria->execute();
        // SELECT * FROM `table` WHERE (`table`.`abonent_id` = 4)
        $rigthSql   = preg_match('/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\(\2\.`abonent_id` = 8\)$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // Multiple condition isEqual with OR-Condition without all equales 
        $criteria   = new TestMysqlCriteriaConcreteConditionIsEqualInCondition();
        $criteria->setData( new TestMysqlData() );

        $sql        = $criteria->execute();
        // SELECT * FROM `table`
        $rigthSql   = preg_match('/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        $this->result('Mysql condition isEqual()', 'ok');
    }
    
    public function test_conditionEqualNegation(){
        // Multiple condition isEqual() and equal() with negation params 
        $criteria   = new TestMysqlCriteriaConcreteEqualNegation();
        $criteria->setData( new TestMysqlData() );
        $criteria->setAbonentId(67);
        $criteria->setServiceId(1);
        $criteria->setTitle( $title = "This's hard string\w\t\r\n");

        $sql        = $criteria->execute();
        // SELECT * FROM `table` WHERE `table`.`title` <> 'This\'s hard string' AND `table`.`service_id` <> 1 AND `table`.`abonent_id` = 67
        $rigthSql   = preg_match($pattern = '/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`title` <> \'' . preg_quote(mysql_escape_string($title)) . '\' AND \2\.`service_id` <> 1 AND \2\.`abonent_id` = 67$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // Multiple condition isEqual() and equal() with negation params without title 
        $criteria   = new TestMysqlCriteriaConcreteEqualNegation();
        $criteria->setData( new TestMysqlData() );
        $criteria->setAbonentId(67);
        $criteria->setServiceId(1);

        $sql        = $criteria->execute();
        // SELECT * FROM `table` WHERE `table`.`service_id` <> 1 AND `table`.`abonent_id` = 67
        $rigthSql   = preg_match($pattern = '/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`service_id` <> 1 AND \2\.`abonent_id` = 67$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        $this->result('Mysql condition equal() negation', 'ok');
    }
    
    public function test_conditionIn(){
        // Multiple condition in() and in() negation 
        $criteria   = new TestMysqlCriteriaConcreteIn();
        $criteria->setData( new TestMysqlData() );
        $criteria->setTitle( array('one', 'two', "thre'e"));    // IN
        $criteria->setServiceId(array(25));                     // IN
        $criteria->setAbonentId(array(43, 42, 41));             // NOT IN

        $sql        = $criteria->execute();
        // SELECT * FROM `table` WHERE `table`.`title` IN ('one','two','thre\'e') AND `table`.`service_id` IN (25) AND `table`.`abonent_id` NOT IN (43,42,41)
        $rigthSql   = preg_match($pattern = '/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`title` IN \(\'one\',\'two\',\'thre\\\\\'e\'\) AND \2\.`service_id` IN \(25\) AND \2\.`abonent_id` NOT IN \(43,42,41\)$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // Multiple condition in() empty array  
        $criteria   = new TestMysqlCriteriaConcreteInEmpty();
        $criteria->setData( new TestMysqlData() );
        $criteria->setServiceId(array());                     // IN

        try {
            $sql        = $criteria->execute();
            equal(false);
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::CONDTITION_IN_EMPTY) throw $e;
        }
        
        // Multiple condition in() not isset param
        $criteria   = new TestMysqlCriteriaConcreteInEmpty();
        $criteria->setData( new TestMysqlData() );

        try {
            $sql        = $criteria->execute();
            equal(false);
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::CONDTITION_IN_NOT_ARRAY) throw $e;
        }
        
        // Multiple condition isIn() not isset param
        $criteria   = new TestMysqlCriteriaConcreteIsIn();
        $criteria->setData( new TestMysqlData() );

        $sql        = $criteria->execute();
        // SELECT * FROM `table`
        $rigthSql   = preg_match($pattern = '/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // Multiple condition isIn() empty params
        $criteria   = new TestMysqlCriteriaConcreteIsIn();
        $criteria->setData( new TestMysqlData() );
        $criteria->setServiceId(array());

        $sql        = $criteria->execute();
        // SELECT * FROM `table`
        $rigthSql   = preg_match($pattern = '/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));

        // Multiple condition isIn() isset params
        $criteria   = new TestMysqlCriteriaConcreteIsIn();
        $criteria->setData( new TestMysqlData() );
        $criteria->setServiceId(array(1, 2));

        $sql        = $criteria->execute();
        // SELECT * FROM `table` WHERE `table`.`service_id` IN (1,2)
        $rigthSql   = preg_match($pattern = '/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`service_id` IN \(1,2\)$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        $this->result('Mysql condition in()', 'ok');
    }
    
    public function test_criteriaOrder(){
        
        // Order without where
        $criteria   = new TestMysqlCriteriaConcreteOrder();
        $criteria->setData( new TestMysqlData() );

        $sql        = $criteria->execute();
        // SELECT * FROM `table` ORDER BY `table`.`servce_id` ASC, `table`.`abonent_id` DESC
        $rigthSql   = preg_match($pattern = '/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sORDER BY\s\2\.`service_id` ASC, \2\.`abonent_id` DESC$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));

        // Order with where
        $criteria   = new TestMysqlCriteriaConcreteOrder();
        $criteria->setData( new TestMysqlData() );
        $criteria->setServiceId(array(1,3));

        $sql        = $criteria->execute();
        // SELECT * FROM `table` WHERE `table`.`service_id` IN (1,3) ORDER BY `table`.`servce_id` ASC, `table`.`abonent_id` DESC
        $rigthSql   = preg_match($pattern = '/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE \2\.`service_id` IN \(1,3\)\sORDER BY\s\2\.`service_id` ASC, \2\.`abonent_id` DESC$/', $sql, $match);
        equal($rigthSql === 1, 'Неправильно построен sql-запрос: ' . var_export($sql, true));
        
        $this->result('Mysql criteria order()', 'ok');
    }
    
    public function test_criteriaHeader(){
        Mysql::instance()->setTablePrefix('');
        $criteria   = new TestMysqlCriteriaConcreteCondtitionIsEqual();
        $criteria->setData( new TestMysqlData() );
        
        // Поле id не найдено
        $criteria->setCriteriaHead('id');
        try{
            $criteria->execute();
            equal(false);
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::NOT_FIND_FIELD_IN_STRUCTURE) throw $e;
        }
        
        // Выбор по одному полю
        $criteria->setCriteriaHead('service_id');
        equal('SELECT `test_mysql_data`.`service_id` FROM `test_mysql_data`' === $criteria->execute(), $criteria->execute());
        
        // Выбор по двум полям
        $criteria->setCriteriaHead('service_id', 'abonent_id');
        equal('SELECT `test_mysql_data`.`service_id`, `test_mysql_data`.`abonent_id` FROM `test_mysql_data`' === $criteria->execute());
        
        // Выбор всех полей
        $criteria->setCriteriaHead();
        equal('SELECT `test_mysql_data`.`service_id`, `test_mysql_data`.`abonent_id`, `test_mysql_data`.`date`, `test_mysql_data`.`amount`, `test_mysql_data`.`comment`, `test_mysql_data`.`title`, `test_mysql_data`.`post`, `test_mysql_data`.`dateNull` FROM `test_mysql_data`' === $criteria->execute());
        
        $this->result('Mysql criteria head()', 'ok');
    }
}

$test   = new TestMysqlCriteria();
$test->complete();

?>