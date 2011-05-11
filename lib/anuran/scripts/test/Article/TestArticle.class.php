<?php

// XXX: Обязательно добавить в тест ObjectComponent
/*
$component      = ArticleComponent::create();
$article        = $component->getData();
$article->user_id  = 'topas';
$component->setData($article);
$article        = $component->getData();
$article->user_id === 'topas';


$article        = ArticleComponent::create()->getData();
$article->user_id !== 'topas';
*/

require_once(dirname(__FILE__) . '/../TestHead.php');
require_once(PATH_MODEL . '/article/ArticleComponent.class.php');

class ArticleTemplate extends Template
{
    
}

class TestArticle extends Test
{
    protected $detail   = false;

    /**
     * @var Mysql
     */
    private $mysql      = null;
    
    /**
     * @var ArticleComponent
     */
    private $component  = null;
    
    public function __construct(){
        $this->mysql        = Mysql::instance();
        ArticleComponent::$template = new ArticleTemplate();
        $this->component    = ArticleComponent::create();
        
        $article            = $this->component->getData();
    
        $this->mysql->createTable($article);
        $this->mysql->dropTable($article);
        $this->mysql->createTable($article);
    }
    
    public function test_ArticleComponentExtends(){
        equal ($this->component instanceof ObjectComponent, "ArticleComponent должен наследоваться от ObjectComponent");
        
        $list    = $this->component->getData()->createList();
        
        equal($list instanceof MysqlList, "ArticleDataComponent->createList() создает объект не похожий на MysqlList: ". get_class($list));
        equal ($list->getIterator() instanceof MysqlIterator, "Неверно вернулся итератор.");
        
        $this->result("ArticleComponent extends", 'ok');
    }

    public function test_createArticle(){
        $article    = $this->component->getData();
        $mysql      = $this->mysql;
        
        // Проверка на автодату
        $article->title = 'some title 2';
        $article->text  = 'some text';
        $article->save();
        equal(date('Y-m-d H:i:s') === $article->date, "Автодата выставлена неверно: " . var_export($article->date, true));
        
        // Проверка на дату (вдруг автодата сработала там где не надо
        $article    = $this->component->getData();
        $someDate   = '1999-01-01 00:00:00';
        $article->title = 'check date';
        $article->text  = 'check date';
        $article->date  = $someDate;
        $article->save();
        $some_id    = $article->id;
        
        $article            = $this->component->getData();
        $article->some_id   = $some_id; 
        $mysql->get($article, 'date as myDate', 'WHERE `id`=:some_id: LIMIT 1');
        equal($article->myDate === $someDate, "Дата некорректно записана в БД: " . var_export($article->myDate, true));

        // Проверка на добавление статьи с заданым Id, которого нет в таблице.
        $article        = $this->component->getData();
        $mysql->get($article, 'max(id) as max_id', 'LIMIT 1');
        
        $unknow_id      = $article->max_id + 1;
        $article        = $this->component->getData();
        $article->title = 'some title2';
        $article->text  = 'some text2 some text2 some text2 some text2';
        $article->id    = $unknow_id;
        try{
            $article->save();
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::EXPECT_MODIFY) throw $e;
        }
        
        // Проверка на заполненность поля title
        $article        = $this->component->getData();
        $article->title = '';
        $article->text  = 'Пустой title';
        try{
          $article->save();
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::NOT_NULL_REQUEST) throw $e;
        }
        
        
        // Проверка на заполненность поля text
        $article        = $this->component->getData();
        $article->title = 'Пустой text';
        $article->text  = '';
        try{
          $article->save();
          equal(false, 'Сохранение в БД пустого текста');
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::NOT_NULL_REQUEST) throw $e;
        }
        
        // Проверка корректности длины поля title
        $article        = $this->component->getData();
        $titleLenght    = "zxc";
        $titleLenght    = str_pad($titleLenght,256,"qwe asd");
        $article->title = $titleLenght;
        $article->text  = "longTitle";
        try{
            $article->save();
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::ERROR_FIELD_LENGTH) throw $e;
        }
        
        // Проверка корректности длины поля text
        $article       = $this->component->getData();
        $textLenght    = "zxc";
        $textLenght    = str_pad($textLenght, 65536+1, "qwe asd");
        $article->text = $textLenght;
        $article->title  = "longTitle";
        try{
            $article->save();
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::ERROR_FIELD_LENGTH) throw $e;
        }
        
        
        $this->result("Create article", 'ok');
    }
    
    public function test_massCreateArticle(){
        $count_off_article  = 10;
        
        $article    = $this->component->getData();
        
        $this->mysql->get($article, 'count(*) as count');
        $count_old  = $article->count;
        
        // Сохранение объектов по отдельности
        for ($i = 0; $i < $count_off_article; $i++){
            $article = $this->component->getData();
            $article->title = "Простой заголовок #" . $i;
            $article->text = "TEXT #$i TEXT #$i TEXT #$i TEXT #$i TEXT #$i TEXT #$i TEXT #$i TEXT #$i TEXT #$i TEXT #$i TEXT #$i TEXT #$i ";
    
            $article->save();
        }
        
        $this->mysql->get($article, 'count(*) as count');
        $count_new  = $article->count;
        equal(($count_old + $count_off_article) == $count_new, "Должно создаться ровно $count_off_article статей: " . ($count_new - $count_old));
    
        // Сохранение объектов списком
        $list     = $this->component->getList();
        for ($i = 0; $i < $count_off_article; $i++){
            $article = $this->component->getData();
            $article->title = "Добавление скопом #" . $i;
            $article->text = "TEXT #$i TEXT #$i TEXT #$i TEXT #$i TEXT #$i TEXT #$i TEXT #$i TEXT #$i TEXT #$i TEXT #$i TEXT #$i TEXT #$i ";
        
            $list->add($article);
        }
        $this->mysql->inserts($list);

        $this->mysql->get($article, 'count(*) as count');
        $count_new  = $article->count;
        equal(($count_old + $count_off_article * 2) == $count_new, "Должно создаться ровно $count_off_article статей: " . ($count_new - $count_old));
        
        $this->result("Create mass-article", 'ok');
    }
    
    public function test_ArticleListComponent()
    {
        $articleList   = $this->component->getList();
        $articleData   = $this->component->getData();
        
        // Тест ф-ии add()
        $articleList->add($articleData);
        try {
            $articleList->add($this);
            throw new Exception("ArticleListComponent->add() должен принимать объекты наследованные от ArticleDataComponent", E_USER_ERROR);    
        }
        catch (Exception $e) {
            equal($e->getCode() !== E_USER_ERROR, $e->getMessage());
            equal($e->getCode() === E_RECOVERABLE_ERROR, $e->getMessage());
        }
        
        $this->result("ArticleListComponent", 'ok');
    }
    
    public function test_MassAccessor(){
        $accessor   = $this->component->getAccessor();
        $criteria   = new ObjectCriteriaComponent();
        $criteria->setPageCount(100);
        $criteria->setStatus(array());
        $criteria->setDate( date('Y-m-d') );
        
        $list   = $accessor->listByCriteria($criteria);
        equal($list->count() === 21, __LINE__);

        // Это простой комментарий fuck afasdf fuck fuck fuck 
        foreach ($list as $item) {
            $item->title   = 'New Title #' . $item->id;
            $item->id      = null;
        }
        $this->mysql->inserts($list);
        $this->result("Mass Accessor", 'ok');
    }
    
    public function test_Accessor(){
        $accessor   = $this->component->getAccessor();
        $accessor->setLimit(100);
        $list   = $accessor->listByDate(date('Y-m-d'));
        
        foreach ($list as $item) {
        	$item->title   = 'Title #' . $item->id;
        	$item->id      = null;
        	$item->save();
        }
        $this->result("Accessor", 'ok');
    }
    
    public function test_Criteria(){
        // Without limit
        $accessor   = $this->component->getAccessor();
        
        $criteria   = new ObjectCriteriaComponent();
        $criteria->setData( $accessor->getData() );
        
        $sql        = $criteria->execute();
        // SELECT * FROM `table` 
        $rigthSql   = preg_match($pattern = '/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`status`\sIN\s\(1\)$/', $sql, $match);
        equal($rigthSql === 1, __LINE__ . ': неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // With page and without count_page
        $criteria   = new ObjectCriteriaComponent();
        $criteria->setData( $accessor->getData() );
        $criteria->setPage(1);
        
        $sql        = $criteria->execute();
        // SELECT * FROM `table` 
        $rigthSql   = preg_match($pattern = '/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`status`\sIN\s\(1\)$/', $sql, $match);
        equal($rigthSql === 1, __LINE__ . ': неправильно построен sql-запрос: ' . var_export($sql, true));

        // With page and count_page
        $criteria   = new ObjectCriteriaComponent();
        $criteria->setData( $accessor->getData() );
        $criteria->setPage(1);
        $criteria->setPageCount(7);
        
        $sql        = $criteria->execute();
        // SELECT * FROM `table` LIMIT 0, 7
        $rigthSql   = preg_match($pattern = '/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`status`\sIN\s\(1\)\sLIMIT 0, 7$/', $sql, $match);
        equal($rigthSql === 1, __LINE__ . ': неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // With page = 0 and count_page
        $criteria   = new ObjectCriteriaComponent();
        $criteria->setData( $accessor->getData() );
        $criteria->setPage(0);
        $criteria->setPageCount(7);
        
        $sql        = $criteria->execute();
        // SELECT * FROM `table` LIMIT 0, 7 
        $rigthSql   = preg_match($pattern = '/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`status`\sIN\s\(1\)\sLIMIT 0, 7$/', $sql, $match);
        equal($rigthSql === 1, __LINE__ . ': неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // With page = -1 and count_page
        $criteria   = new ObjectCriteriaComponent();
        $criteria->setData( $accessor->getData() );
        $criteria->setPage(-1);
        $criteria->setPageCount(7);
        
        $sql        = $criteria->execute();
        // SELECT * FROM `table` LIMIT 0, 7
        $rigthSql   = preg_match($pattern = '/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`status`\sIN\s\(1\)\sLIMIT 0, 7$/', $sql, $match);
        equal($rigthSql === 1, __LINE__ . ': неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // Without page and with count_page
        $criteria   = new ObjectCriteriaComponent();
        $criteria->setData( $accessor->getData() );
        $criteria->setPageCount(7);
        
        $sql        = $criteria->execute();
        // SELECT * FROM `table` LIMIT 0, 7
        $rigthSql   = preg_match($pattern = '/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`status`\sIN\s\(1\)\sLIMIT 0, 7$/', $sql, $match);
        equal($rigthSql === 1, __LINE__ . ': неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // Without page and with count_page = 0
        $criteria   = new ObjectCriteriaComponent();
        $criteria->setData( $accessor->getData() );
        $criteria->setPageCount(0);
        
        $sql        = $criteria->execute();
        // SELECT * FROM `table`
        $rigthSql   = preg_match($pattern = '/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`status`\sIN\s\(1\)$/', $sql, $match);
        equal($rigthSql === 1, __LINE__ . ': неправильно построен sql-запрос: ' . var_export($sql, true));

        // With page = 3 and with count_page = 7
        $criteria   = new ObjectCriteriaComponent();
        $criteria->setData( $accessor->getData() );
        $criteria->setPage(3);
        $criteria->setPageCount(7);
        
        $sql        = $criteria->execute();
        // SELECT * FROM `table` LIMIT 14, 7
        $rigthSql   = preg_match($pattern = '/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`status`\sIN\s\(1\)\sLIMIT 14, 7$/', $sql, $match);
        equal($rigthSql === 1, __LINE__ . ': неправильно построен sql-запрос: ' . var_export($sql, true));
        
        // With page = 5 and with count_page = 7 and status expect 3
        $criteria   = new ObjectCriteriaComponent();
        $criteria->setData( $accessor->getData() );
        $criteria->setPage(5);
        $criteria->setPageCount(7);
        $criteria->setStatus(array());
        $criteria->setExpectStatus(3);
        
        $sql        = $criteria->execute();
        // SELECT * FROM `table` WHERE `table`.`status` NOT IN (3) LIMIT 28, 7
        $rigthSql   = preg_match($pattern = '/^SELECT ((`\w+`)\.`\w+`[,\s]*)+\sFROM\s\2\sWHERE\s\2\.`status` NOT IN \(3\)\sLIMIT 28, 7$/', $sql, $match);
        equal($rigthSql === 1, __LINE__ . ': неправильно построен sql-запрос: ' . var_export($sql, true));
        
        $this->result("Criteria", 'ok');
    }
    
    public function __destruct(){
        if ($this->clearDB){
            $this->mysql->dropTable($this->component->getData());
        }
    }

}

$test = new TestArticle();
$test->complete();

?>