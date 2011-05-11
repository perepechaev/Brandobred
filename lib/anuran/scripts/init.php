<?php

require_once(PATH_CORE . '/assert.php');
require_once(PATH_MODEL . '/article/ArticleComponent.class.php');
require_once(PATH_MODEL . '/user/UserAuthorize.class.php');
require_once(PATH_CONTROLLER . '/UserController.class.php');
require_once(PATH_CONTROLLER . '/ArticleController.class.php');
require_once(PATH_CONTROLLER . '/NewsController.class.php');
require_once(PATH_CONTROLLER . '/CatalogController.class.php');
require_once(PATH_CONTROLLER . '/AuctionController.class.php');
require_once(PATH_CONTROLLER . '/AuctionTrainingController.class.php');
require_once(PATH_CONTROLLER . '/PollingController.class.php');
require_once(PATH_PAGE_MODEL . '/user/AuctionUserData.class.php');
require_once(PATH_PAGE_MODEL . '/user/AuctionUserGroupData.class.php');
require_once(PATH_PAGE_MODEL . '/auction/AuctionLotData.class.php');
require_once(PATH_PAGE_MODEL . '/article/AuctionArticle.class.php');
require_once(PATH_PAGE_MODEL . '/image/ImageComponent.class.php');



class InitAuction
{
    /**
     * @var Mysql
     */
    private $mysql      = null;
    
    /**
     * @var ArticleComponent
     */
    private $component  = null;
    
    /**
     * @var AuctionNewsData
     */
    private $news       = null;
    
    public function __construct(){
        $this->mysql        = Mysql::instance();
        $this->component    = ArticleController::create()->getComponent();
        
        $this->news         = NewsController::create()->getComponent()->getData();
        $this->mysql->createDbIfNotExists();
        $this->mysql->useDb();
    }
    
   
    public function creatTable(){           
        
        $article        = $this->component->getData();
        $news           = $this->news;
        
        $this->mysql->createTable($article);
        $this->mysql->alterTable($article);
        $this->mysql->get($article, 'count(*) as count');
        if ( $article->count > 0 ) {
            return false;
        }
        
        $news->clean();
        $news->title    = 'Первыя новость';
        $news->text     = 'Текст перовй новости';
        $news->status   = ObjectComponent::STATUS_APPROVE;
        $news->save();        
        
        
        $news->clean();
        $news->title    = 'Вторая новость';
        $news->text     = 'Текст второй новости';
        $news->status   = ObjectComponent::STATUS_APPROVE;
        $news->save();        
        
        
        $news->clean();
        $news->title    = 'Третья новость';
        $news->text     = 'Текст третей новости';
        $news->status   = ObjectComponent::STATUS_APPROVE;
        $news->save();        
        
        $article->clean();
        $article->title  = 'Главная';
        $article->text   = 'Описание всего проекта.';
        $article->status = ObjectComponent::STATUS_APPROVE;
        $article->save();    
    
        $article->clean();
        $article->title = 'Новичкам';
        $article->text  = 'Свод правил, вводные инструкции новичкам.';
        $article->status = ObjectComponent::STATUS_APPROVE;
        $article->save();
    

        $article->clean();
        $article->title = 'Изменения';
        $article->text  = 'Изменения правил сайта, правил торгов.';
        $article->status = ObjectComponent::STATUS_APPROVE;
        $article->save();
    
        
        $article->clean();
        $article->title = 'faq';
        $article->text  = 'Часто задаваемые вопросы пользователей.';
        $article->status = ObjectComponent::STATUS_APPROVE;
        $article->save();
            
        
        $article->clean();
        $article->title = 'Покупки за сегодня.';
        $article->text  = 'Список вещей проданых с аукциона и их стоимость.';
        $article->status = ObjectComponent::STATUS_APPROVE;
        $article->save();
    }

     public function creatAuctionLotTable(){
         $component     = AuctionController::create()->getComponent();
         
         $lot           = $component->getLotData();
         $this->mysql->createTable($lot);
         $this->mysql->alterTable($lot);
    }
    
     public function creatAuctionTrainingLotTable(){
         $component     = AuctionTrainingController::create()->getComponent();
         
         $lot           = $component->getLotData();
         $this->mysql->createTable($lot);
         $this->mysql->alterTable($lot);
    }
    
    
    public function createUser(){
        $component      = UserController::create()->getComponent();
        
        $user           = $component->getData();
        $this->mysql->createTable($user);
        $this->mysql->alterTable($user);
        
        $this->mysql->createTable($component->getPointData());
        $this->mysql->alterTable($component->getPointData());
        
        $this->mysql->query("UPDATE `user` SET status = 1 WHERE status IS NULL");
        
        $group          = new AuctionUserGroupData($component);
        $this->mysql->createTable($group);
        $this->mysql->alterTable($group);
        
        $this->mysql->get($group, 'count(*) as count');
        if ( $group->count === 0 ) {
            $group->clean();
            $group->id      = UserComponent::USER_ROLE_ADMIN;
            $group->title   = 'Администраторы';
            $this->mysql->insert($group);
            
            $group->clean();
            $group->id      = UserComponent::USER_ROLE_GUEST;
            $group->title   = 'Гости';
            $this->mysql->insert($group);
                    
            $group->clean();
            $group->id      = UserComponent::USER_ROLE_MODERATOR;
            $group->title   = 'Модераторы';
            $this->mysql->insert($group);
            
            $group->clean();
            $group->id      = UserComponent::USER_ROLE_USER;
            $group->title   = 'Пользователи';
            $this->mysql->insert($group);
        }
        
        $point          = $component->getPointData();
        $this->mysql->createTable($point);
        $this->mysql->alterTable($point);
        
        // Создание модератора
        if ($user->getAccessor()->getByLogin('moderator') === false){
            $user->clean();
            $user->login        = 'moderator';
            $user->password     = 'moderator';
            $user->name         = 'Администратор';
            $user->lastName     = '';
            $user->patronymic   = '';
            $user->guest_login  = uniqid();
            $user->guest_pwd    = md5(uniqid());
            $user->mail         = 'admin@' . URL_SITE;
            $user->status       = ObjectComponent::STATUS_APPROVE;
            $user->role_id      = UserComponent::USER_ROLE_MODERATOR;
            $user->save();
        }
        
        // Таблица платежей
        $cash   = new AuctionUserCash();
        $this->mysql->createTable($cash);
        $this->mysql->alterTable($cash);
    }
    
    public function createCatalog(){
        $component      = CatalogController::create()->getComponent();

        $this->createCatalogCategory($component);
        $this->createCatalogElements($component);
    }
    
    private function createCatalogCategory(AuctionCatalog $component){

        $group          = $component->getCategoryData();
        $this->mysql->createTable($group);
        $this->mysql->alterTable($group);
        
        $this->mysql->get($group, 'count(*) as count1, "1" as id');
        if ( $group->count1 > 0 ) {
            return false;
        }
        
        $group->clean();
        $group->title   = 'Компьютеры';
        $group->status  = ObjectComponent::STATUS_APPROVE;
        $group->save();
        
        $group->clean();
        $group->title   = 'Сотовые телефоны';
        $group->status  = ObjectComponent::STATUS_APPROVE;
        $group->save();
        
        $group->clean();
        $group->title   = 'Компьютерные аксессуары';
        $group->status  = ObjectComponent::STATUS_APPROVE;
        $group->save();
    }

    private function createCatalogElements(AuctionCatalog $component){
        $stake  = AuctionController::create()->getComponent()->getStakeData();
        $this->mysql->createTable( $stake);

        $element  = $component->getData();
        $this->mysql->createTable($element);
        $this->mysql->alterTable($element);
        
        $description    = $component->getDescriptionData();
        $this->mysql->createTable($description);
        $this->mysql->alterTable($description);
        try{
            $this->mysql->get($element, 'count(*) as count, "1" as id');
            if ( $element->count > 0 ) {
                return false;
            }
        }
        catch (MysqlException $e){
            equal($e->getCode() === MysqlException::ERROR_QUERY);
        }
        
        $group1   = $component->getCategoryData()->getAccessor()->getByTitle('Компьютеры');
        $group2   = $component->getCategoryData()->getAccessor()->getByTitle('Сотовые телефоны');

        $elements = array(
            $group1->id   => array('Pentium 100', 'Pentium 133', 'Pentium 200', 'Pentium 233', 'Pentium 266'),
            $group2->id   => array('Simiens A60', 'Simiens C60', 'Simiens A70', 'Simiens A72', 'Nokia 1101', 'Samsung 300'),
        );       
        
        $lot    = AuctionController::create()->getComponent()->getLotData();
        $this->mysql->createTable($lot);
        $this->mysql->alterTable($lot);
        
        foreach ($elements as $group_id => $item) {
            foreach ($item as $title) {
                $element->clean();
                $element->title    = $title;
                $element->group_id = $group_id;
                $element->status   = ObjectComponent::STATUS_APPROVE;
                $element->price    = '1.00';
                $element->state    = AuctionCatalog::STATE_ON;
                $element->save();
                $lot->clean();
                $lot->create($element);
                $lot->auctioned();
            }
        }
        
    }
    
    public function createImage(){
        $data   = ImageComponent::create()->getData();
        $this->mysql->createTable($data);
        $this->mysql->alterTable($data);
    }
    
    public function createPolling(){
        $component  = PollingController::create()->getComponent();
        
        $this->mysql->createTable( $component->getData() );
        $this->mysql->alterTable( $component->getData() );
        
        $answer = $component->getAnswerData();
        $this->mysql->createTable($answer);
        $this->mysql->alterTable($answer);
        
        $user   = $component->getUserData();
        $this->mysql->createTable($user);
        $this->mysql->alterTable($user);
    }
    
    public function createArticleImage(){
        $image  = AuctionArticleImage::create();
        Mysql::instance()->createTable($image);
        Mysql::instance()->alterTable($image);
    }
    
    public function createAutoStake(){
        $autoStake  = AuctionController::create()->getComponent()->getAutoStake();
        Mysql::instance()->createTable( $autoStake );
        Mysql::instance()->alterTable( $autoStake );
    }
    
}

try {
    $init   = new InitAuction();
    $init->createArticleImage();
    $init->creatTable();
    $init->createUser();
    $init->createCatalog();
    $init->creatAuctionLotTable();
    $init->creatAuctionTrainingLotTable();
    $init->createImage();
    $init->createPolling();
    $init->createAutoStake();
    echo "Create table is ok.\n";
    
    if (!is_dir(PATH_UPLOAD)){
        mkdir(PATH_UPLOAD, 0777, true);
        echo "Create upload dir is ok\n";
    }
} catch (Exception $e) {
    echo "Not complete:\n";
    if (function_exists('iconv')){
        $message    = iconv('UTF-8', TEST_OUTPUT_ENCODE, $e->getMessage());
    }
    else {
        $message    = $e->getCode();
    }
    echo get_class($e) . "({$e->getCode()}):" . $message . "\n";
    echo $e->getTraceAsString();
}

?>