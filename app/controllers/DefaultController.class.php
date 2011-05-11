<?php

require_once 'app/model/brand/Brand.class.php';
require_once 'app/model/brand/BrandTemplate.class.php';
require_once 'app/model/brand/BrandTerms.class.php';

require_once 'app/model/tag/Tag.class.php';
require_once 'app/model/tag/TagMap.class.php';

require_once 'app/model/tag/TagCensure.class.php';
require_once 'app/model/tag/TagCensureMap.class.php';

require_once 'app/model/user/UserPost.class.php';

require_once 'lib/anuran/model/file/FileUpload.class.php';

require_once 'lib/rin/UTF8.php';
require_once 'lib/rin/censure.php';

class DefaultController extends Controller
{

    /**
     * @var BrandTemplate
     */
    private $view;

    public function blockDefault(){
        if (isset($_POST['id'])){

            $this->blockStoreTag();
            PageException::pageRedirect('/');
        }

        $this->blockShowRandom();
        
        if (isset($_SESSION['user'])){
            $_SESSION['user']['twitter']['sent'] = false;    
            $_SESSION['user']['facebook']['sent'] = false;    
            $_SESSION['user']['livejournal']['sent'] = false;    
        }
        
        //        $this->blockResult();

        return $this;
    }

    /**
     * Страница бренда
     *
     * @param $id
     * @return DefaultController
     */
    public function blockBrandById($id){
        
        $brand = BrandMap::instance()->getById($id);

        if (!$brand){
            PageException::pageNotFound();
        }
        
        if ($brand->status !== Status::APPROVE){
            if (!UserController::create()->isAdmin()){
                PageException::pageNotFound();
            }
            $this->blockBrandWaiting($id);
            return $this;
        }

        $tags  = BrandTagMap::instance()->listByBrandId($id);

        $this->view->pageBrand($brand, 'association');
        $this->view->cloudTags($tags, 'name');
        
        return $this;
    }
    
    public function blockBrandCommentById($brand_id){
        $brand = BrandMap::instance()->getById($brand_id);

        if ($brand->status !== Status::APPROVE){
            if (!UserController::create()->isAdmin()){
                PageException::pageNotFound();
            }
            $this->blockBrandWaiting($brand_id);
            return $this;
        }
        
        if (isset($_REQUEST['postbrand'])){
            if ($this->validateUserComment() && $this->actionStoreTagComment(0, $brand_id)){
                PageException::pageBackRedirect();
            }
            
            $this->addHtml('<h3 style="color:red">Введены не все поля</h3>');
            
        }

        $comments = PostTagMap::instance()->listPostByBrandId($brand_id);
        $user     = UserSession::instance()->isAuthorize() ? UserSession::instance()->getUser() : new User();

        $this->view->pageBrand($brand, 'comment');
        $this->view->showPostForm($user, $_POST);
        $this->view->showPosts($comments);
        
        return $this;
    }
    
    public function blockPostList(){
        $this->requestAdminAuthorize();
        
        $posts = PostTagMap::instance()->listDirty();
        
        $this->addHtml('<h1>Список непроверенных постов</h1>');
        $this->view->commentControlMass();
        $this->view->showPosts($posts);
        
        return $this;
    }
    
    public function blockPostApprove($id){
        $this->requestAdminAuthorize();
        
        $post = PostTagMap::instance()->getById($id);
        if ($post->status !== Status::APPROVE){
            $post->status = Status::APPROVE;
            $post->save();
        }

        PageException::pageBackRedirect();
    }
    
    public function blockPostDisapprove($id){
        $this->requestAdminAuthorize();
        
        $post = PostTagMap::instance()->getById($id);
        if ($post->status !== Status::DISAPPROVE){
            $post->status = Status::DISAPPROVE;
            $post->save();
        }

        PageException::pageBackRedirect();
    }
    
    public function blockPostMassApprove(){
        $this->requestAdminAuthorize();
        
        $posts = PostTagMap::instance()->listDirty();
        foreach ($posts as $post){
            if ($post->status !== Status::APPROVE){
                $post->status = Status::APPROVE;
                $post->save();
            }
        }
        
        PageException::pageBackRedirect();
    }

    public function blockPostMassDisapprove(){
        $this->requestAdminAuthorize();
        
        $posts = PostTagMap::instance()->listDirty();
        foreach ($posts as $post){
            if ($post->status !== Status::DISAPPROVE){
                $post->status = Status::DISAPPROVE;
                $post->save();
            }
        }
        
        PageException::pageBackRedirect();
    }

    /**
     * Страница бренда ожидающего модерацию
     *
     * @param $id
     * @return DefaultController
     */
    public function blockBrandWaiting($id){
        $this->addBlock('brandStatus', BrandMap::instance()->getById($id));

        return $this;
    }

    /**
     * Список брендов ожидающих модерации
     *
     * @return DefaultController
     */
    public function blockBrandsDirty(){
        $this->requestAdminAuthorize();

        $this->blockBrandSearchForm();

        if (isset($_REQUEST['search'])){
            $criteria = new BrandCriteria();
            $criteria->setSearchTitle($_REQUEST['brand']);
            $criteria->setStatus(Status::DIRTY);
            $criteria->join(new BrandTaq(), 'id', 'brand_id', 'count(tag_id) as count');

            $brands = BrandMap::instance()->listByCriteria($criteria);
        }
        else{
            $criteria = new BrandCriteria();
            $criteria->setStatus(Status::DIRTY);
            $criteria->join(new BrandTag(), 'id', 'brand_id', 'count(tag_id) as count');
            $criteria->setCrtieraGroup('id');

            $brands = BrandMap::instance()->listByCriteria($criteria);
        }

        if (!$brands->count()){
            $this->addHtml('<h1>Ничего не найдено</h1>');
            return $this;
        }

        $this->addBlock('brandList', $brands);

        return $this;
    }

    public function blockBrandsCloud(){
        $brands = BrandMap::instance()->listWithCountTag();

        $this->view->cloudBrands($brands);

        return $this;
    }

    /**
     * Action - одобрить бренд
     *
     * @param $id
     * @return DefaultController
     */
    public function blockBrandApprove($id){
        $this->requestAdminAuthorize();
        $brand = BrandMap::instance()->getById($id);

        if ($brand->status !== Status::APPROVE){
            $brand->status = Status::APPROVE;
            $brand->save();
            PageException::pageRedirect('/brand/' . $id . '/');
        }

        PageException::pageRedirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * Action - отклонить бренд
     *
     * @param $id
     * @return DefaultController
     */
    public function blockBrandDisapprove($id){
        $this->requestAdminAuthorize();

        $brand = BrandMap::instance()->getById($id);

        if ($brand->status !== Status::DISAPPROVE){
            $brand->status = Status::DISAPPROVE;
            $brand->save();
        }

        PageException::pageRedirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * Страница редактирования бренда
     *
     * @return DefaultController
     */
    public function blockBrandEdit($id){
        $this->requestAdminAuthorize();


        $brand = BrandMap::instance()->getById($id);

        if (empty($_POST['upload'])){
        }
        else {
            $upload     = new FileUpload();
            $upload->setFile($_FILES, 'logo' );
            if ($upload->isTransfered()){
                if ($this->validateBrandFileUpload($upload, $errors)){
                    $upload->upload( PATH_UPLOAD, $brand->filename = uniqid() . '.png' );
                    $brand->save();
                    $this->actionBrandThumbnail($upload, $brand->filename);
                }
            }

            if ($this->validateBrandEdit($_POST, $errors)){
                $this->actionStoreBrand($brand, $brand->filename);
            }

            if (count($errors) > 0){
                $this->blockBrandStoreError($errors);
            }
        }

        $this->blockShowByBrand($brand);
        $this->addBlock('uploadForm', $brand->getRawValues());

        return $this;
    }

    /**
     * Страница список брендов. Бренды
     * должны сортироваться по приоритету
     *
     * @return DefaultController
     */
    public function blockBrandList(){
    
	$this->view->showBrandListMessage();
        $this->blockBrandSearchForm();

        if (empty($_REQUEST['brand'])){
            $pager  = new StandartPager(isset($_GET['page']) ? $_GET['page'] : 1, 10);

            $brands = BrandMap::instance()->listApproved($pager);
            $this->view->brandList($brands);

            return $this;
        }

        $this->blockBrandSearch();

        return $this;
    }

    /**
     * Поиск бренда по имени
     *
     * @return DefaultController
     */
    public function blockBrandSearch(){
        if (empty($_REQUEST['brand'])){
            $this->blockBrandSearchForm( );
            return $this;
        }

        if (empty($_REQUEST['intag'])){
            $criteria = new BrandCriteria();
            $criteria->setData( new Brand() );
            $criteria->join( new BrandTag(), 'id', 'brand_id', 'count(tag_id) as count');
            $criteria->setCrtieraGroup('id');
        }
        else {
            $criteria =new BrandTagComposeCriteria();
            $criteria->setData( new BrandTag() );
        }

        $criteria->setSearchTitle( $_REQUEST['brand'] );
        $criteria->setStatus(Status::APPROVE);


        $list = Mysql::instance()->listByCriteria($criteria);

        if ($list->count() === 0){
            $this->addHtml('<h1>Нет такого</h1>');
            return $this;
        }

        $this->addHtml(sprintf("<h1>Результат поиска «%s»: </h1>", html($_REQUEST['brand'])));
        $this->addBlock('brandList', $list);

        return $this;
    }

    /**
     * Форма поиска бренда
     *
     * @return DefaultController
     */
    public function blockBrandSearchForm(){
        $search = "";
        if (isset($_REQUEST['search']) && !empty($_REQUEST['brand'])){
            $search = $_REQUEST['brand'];
        }
        $intag = isset($_REQUEST['intag']) ? $_REQUEST['intag'] : 1;

        $this->view->searchForm($search);
        return $this;
    }

    /**
     * Страница загрузка бренда пользователем
     *
     * @return DefaultController
     */
    public function blockBrandUpload(){
        if (empty($_POST['upload'])){
            $this->blockBrandUploadForm();
        }
        else {
            $this->blockBrandStore();
        }
        return $this;
    }

    /**
     * Action сохранение нового бренда
     *
     * @return DefaultController
     */
    public function blockBrandStore(){

        $file       = $_FILES;
        $upload     = new FileUpload();
        $upload->setFile($file, 'logo' );

        if (!$this->validateBrandUpload($upload, $_POST, $errors)){
            $this->blockBrandStoreError($errors);
            $this->blockBrandUploadForm();
            return $this;
        }

        $upload->upload( PATH_UPLOAD, $filename = uniqid() . '.png' );
        $this->actionStoreBrand($brand = new Brand(), $filename);

        $this->actionBrandThumbnail($upload, $filename);

        PageException::pageRedirect(Router::instance()->makeUrl(array('brand', $brand->id, 'waiting')));
    }

    private function actionBrandThumbnail(FileUpload $upload, $filename){
        $param  = array(
            'width'     => 176,
            'height'    => 132,
            'type'      => IMAGETYPE_PNG,
        );
        Thumbnail::output(
        $upload->getFileName(),
        PATH_PUBLIC . '/img/brands/' . $filename,
        $param
        );
    }

    private function validateBrandUpload(FileUpload $upload, $data = array(), &$errors = array()){
        $errors = array();

        $this->validateBrandFileUpload($upload, $errors);
        $this->validateBrandEdit($data, $errors);

        return count($errors) === 0;
    }

    private function validateBrandFileUpload(FileUpload $upload, &$errors = array()){

        if (!$upload->isTransfered()){
            $errors['upload'][] = '<h3 style="color: red">Файл не был загружен</h3>';
            return false;
        }

        if (!$upload->isAllowedType()){
            $errors['upload'][] = '<h3 style="color: red">Разрешено загружать JPG, PNG и GIF</h3>';
            return false;
        }

        return true;
    }

    private function validateBrandEdit($data = array(), &$errors = array()){

        if (empty($data['title'])){
            $errors['title'][] = '<h3 style="color: red">Не заполнено поле с названием</h3>';
        }

        if (empty($data['company'])){
            $errors['company'][] = '<h3 style="color: red">Не указана компания</h3>';
        }

        return empty($errors['company']) && empty($errors['title']);
    }

    private function actionStoreBrand(Brand $brand, $filename = null){
        $isAdmin = UserController::create()->isAdmin();

        $brand->filename = $filename ? $filename : $brand->filename;
        $brand->title    = $_POST['title'];
        $brand->company  = $_POST['company'];
        $brand->industry_id = $_POST['industry_id'];

        if ( $isAdmin ){
            $brand->priority = (int) $_POST['priority'];
        }

        $brand->status   = $isAdmin ? Status::APPROVE : Status::DIRTY;

        try {
            $brand->save();
        }
        catch (MysqlException $e){
            if ($e->getCode() === MysqlException::EXPECT_MODIFY){
                return false;
            }

            throw $e;
        }
        return true;
    }

    /**
     * Форма загрузки бренда
     *
     * @return DefaultController
     */
    public function blockBrandUploadForm(){
        if (empty($_POST['upload'])){
            $post = array(
                'title'         => '',
                'company'       => '',
                'industry_id'   => 1,
                'priority'      => 50
            );
        }
        else{
            $post = $_POST;
        }

        $this->addBlock('uploadForm', $post);
        return $this;
    }

    private function blockBrandStoreError($errors){
        foreach ($errors as $field => $error){
            $this->addHtml( implode('', $error) );
        }
        return $this;
    }

    private function requestAdminAuthorize(){
        if (!UserController::create()->isAdmin()){
            PageException::pageForbidden();
        }
        return true;
    }


    /**
     * Отображает случайный бренд
     *
     * @return DefaultController
     */
    public function blockShowRandom(){


        $brand  = BrandMap::instance()->getRandExceptIds( $this->getExceptBrandIds() );

        if (!$brand && isset($_SESSION['brandskip'])){
            $_SESSION['brandskip'] = array();
            $brand = BrandMap::instance()->getRandExceptIds( $this->getExceptBrandIds() );
        }

        if (!$brand){
            $brand = BrandMap::instance()->getRand();
        }

        if (!$brand){
            $this->addHtml('<h1>Где все?</h1>');
            return $this;
        }

        $this->blockShowByBrand($brand);

        $isSentInTwitter        = empty($_SESSION['user']['twitter']['sent']) ? '' : $_SESSION['user']['twitter']['sent'];
        $isSentInFacebook       = empty($_SESSION['user']['facebook']['sent']) ? '' : $_SESSION['user']['facebook']['sent'];
        $isSentInLivejournal    = empty($_SESSION['user']['livejournal']['sent']) ? '' : $_SESSION['user']['livejournal']['sent'];
        $censured               = empty($_SESSION['user']['last_tag']['censured']) ? false : true;
        $this->view->showTagForm( $brand->id, UserController::create()->getIsUserAuthorize(), $isSentInTwitter, $censured, $isSentInFacebook, $isSentInLivejournal);
        
        $_SESSION['user']['last_tag']['censured'] = 0;

        return $this;
    }

    private function getExceptBrandIds(){
        $ids    = isset($_SESSION['brands']) ? array_keys($_SESSION['brands']) : array();

        if (UserSession::instance()->isAuthorize()){
            Mysql::instance()->queryf('SELECT brand_id as id FROM `user_brand_tag` WHERE `user_id` = %d', UserSession::instance()->getUser());
            $ids = array_merge($ids,  Mysql::instance()->fetchArray(Mysql::FETCH_COLUMN));
        }

        if (isset($_GET['skip']) && $_GET['skip']){
            Session::need();
            $_SESSION['brandskip'][] = $_GET['skip'];
        }

        if (isset($_SESSION['brandskip'])){
            $ids = array_merge($ids, $_SESSION['brandskip']);
        }

        return array_unique($ids);
    }

    public function blockShowById($brand_id){
        try {
            $brand = BrandMap::instance()->getById($brand_id);
           	$this->blockShowByBrand( $brand );
        }
        catch (MysqlException $e){
            if ($e->getCode() === MysqlException::EXPECT_ONE_RECORD){
                $this->addHtml('Элемент был удален');
                return $this;
            }
            throw $e;
        }

        return $this;
    }

    public function blockShowByBrand(Brand $brand){
        $this->addBlock('brandShow', $brand);
        return $this;
    }

    /**
     * Action сохранения пользовательского тэга
     *
     * @return DefaultController
     */
    public function blockStoreTag(){
        if (empty($_POST['tag'])){
            return false;
        }

        Session::need();
         
        $brand = BrandMap::instance()->getById($_POST['id']);
        $tag   = TagMap::instance()->getTagByName($_POST['tag']);

        $link  = BrandTagMap::instance()->getByLink($brand->id, $tag);
        $link->count++;
        $link->update();

        if ( UserSession::instance()->isAuthorize() ){
            $user = UserSession::instance()->getUser();
            $user->storeBrandTags($brand->id, $tag->id);

            // XXX: Выключено сохранение поста в твиттер
            // $user->setPublicOnTwitter(isset($_POST['public_on_twitter']));
        }
        
        $_SESSION['user']['last_tag']['censured'] = $this->actionCensureTag($tag);

        if (!$_SESSION['user']['last_tag']['censured']){
            $this->actionPostExternals($brand, $tag);
        }

        $this->actionStoreTagComment($tag->id, $brand->id);

        Session::need();
        $_SESSION['brands'][$brand->id] = $_POST['tag'];
        $_SESSION['brandtags'][$brand->id] = $tag->id;
    }
    
    private function actionPostExternals(IBrand $brand, Tag $tag){

        $_SESSION['user']['twitter']['sent'] = false;
        $_SESSION['user']['facebook']['sent'] = false;
        $_SESSION['user']['livejournal']['sent'] = false;
        
        $twitter    = TwitterController::create();
        // XXX: Выключена проверка пользователея 
        // if ($twitter->isPublicMessage()){
        if (isset($_POST['public_on_twitter']) && $_POST['public_on_twitter']){
            $link   = 'http://' . URL_SITE . URL_PATH . 'brand/' . $brand->id . '/';

            $twitter->requestAuth();
            // "имя бренда" для меня - это "имя метки" <URL ссылки>. А для вас? #brands
            $twitter->api->statusUpdate($mess = sprintf(
                '„%s” для меня - это „%s” %s. А для вас? #brands'
                , $brand->title, $tag->name, $link));

                $_SESSION['user']['twitter']['sent'] = $mess;
        }
        
        if (isset($_POST['public_on_facebook']) && $_POST['public_on_facebook']){
            $link   = 'http://' . URL_SITE . URL_PATH . 'brand/' . $brand->id . '/';
            
            $facebook = FacebookController::create();
            $facebook->setStatus($mess = sprintf(
                '„%s” для меня - это „%s” %s',
                $brand->title, 
                $tag->name, 
                $link
            ), $_SESSION['user']['facebook']['id']);
                
            $_SESSION['user']['facebook']['sent'] = $mess;
        }
        
        if (isset($_POST['public_on_livejournal']) && $_POST['public_on_livejournal']){
            $link   = 'http://' . URL_SITE . URL_PATH . 'brand/' . $brand->id . '/';
            
            $livejournal = LivejournalController::create();
            $livejournal->setStatus($brand, $tag, UserSession::instance()->getUser());
                
            $_SESSION['user']['livejournal']['sent'] = true;
        }
    }
    
    private function validateUserComment(){
        return !empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['comment']);
    }

    private function actionStoreTagComment($tag_id, $brand_id){

        if (empty($_POST['comment']) && !trim($_POST['comment'])){
            return false;
        }

        $post = new UserPost();
        $post->comment = $_POST['comment'];


        if (UserSession::instance()->isAuthorize()){
            $user = UserSession::instance()->getUser();

            if ($user->isAdmin()){
                $post->status = Status::APPROVE;
            }
            
            if (UserSession::instance()->isOauthAuthorize()){
                $post->status = Status::APPROVE;
            }
            
            if (!empty($_POST['name']) && $user->name !== $_POST['name']){
                $user->name = $_POST['name'];
            }
            
            if (!empty($_POST['email']) && $user->email !== $_POST['email']){
                $user->email = $_POST['email'];
            }
            
            if ($user->isModify()){
                $user->save();
            }
        }
        else {
            $user = new User();
            $user->name = $_POST['name'];
            $user->email = $_POST['email'];
            $user->alias = $_POST['name'];
            $user->save();
            UserController::create()->authorize($user->id);
        }
        
        $post->user_id = $user->id;
        $post->save();

        $link = new PostTag();
        $link->post_id  = $post->id;
        $link->tag_id   = $tag_id;
        $link->brand_id = $brand_id;
        $link->save();

        return true;
    }

    /**
     * Проверка на мат
     *
     * @return bool
     */
    private function actionCensureTag(Tag $tag){

        $GLOBALS['ERROR_SKIP'] = true;
        if (censure($tag->name, 3, '', false) === false){
            $GLOBALS['ERROR_SKIP'] = false;
            return false;
        }
        $GLOBALS['ERROR_SKIP'] = false;

        $censure = TagCensureMap::instance()->getByTagId($tag->id);
        

        if (!$censure){
            $censure = new TagCensure();
            $censure->tag_id = $tag->id;
            $censure->status = Status::DIRTY;
            $censure->save();
            
            $tag->status = Status::DIRTY;
            $tag->save(); 
            
            return true;
        }

        if ($tag->status !== $censure->status){
            $tag->status = $censure->status;
            $tag->save();
        } 

        if ($censure->status === Status::DIRTY){
            return true;
        }

        if ($censure->status === Status::APPROVE){
            return false;
        }

        if ($censure->status === Status::DISAPPROVE){
            $tag->remove();
            return true;
        }
        
        return true;
    }
    
    public function blockTagCensure(){
        $this->requestAdminAuthorize();
        
        $list = TagCensureMap::instance()->listDirty();
        
        $this->addHtml('<h2>Антимат</h2>');
        foreach ($list as $tag){
            $this->view->tagItem($tag);
        }
        $this->addHtml('<div style="margin-top: 2em;"><strong>');
        $this->addHtml( ahref('удалить все теги', 'tags', 'clear') );
        $this->addHtml('</strong></div> ');
        
        return $this;
    }
    
    public function blockTagClear(){
        $this->requestAdminAuthorize();
        
        $list = TagCensureMap::instance()->listDirty();
        
        $this->addHtml('<h2>Антимат</h2>');
        foreach ($list as $tag){
            if ($tag->status !== Status::DISAPPROVE){
                $tag->status = Status::DISAPPROVE;
                $tag->save();
            }
            TagMap::instance()->remove($tag->tag_id);
        }
        
        PageException::pageRedirect('/tags/');
    }

    public function blockResult(){
        if (UserSession::instance()->isAuthorize()){
            $user = UserSession::instance()->getUser();
            $count = $user->brands()->count();
            if (!empty($_SESSION['brands']) && count($_SESSION['brands'])){
                $ids = array_keys($_SESSION['brands']);
                $this->blockResultForBrandById(end($ids));
            }
            return $this;
        }

        if (isset($_SESSION['brands']) && (count($_SESSION['brands']) >= 5)){
            $ids    = array_keys($_SESSION['brands']);
            $this->blockResultForBrandById(end($ids));
        }
        else {
            $this->addHtml('<br />');
            $this->addHtml(sprintf('<div>Для просмотра результатов внесите еще %d тэгов</div>',
            isset($_SESSION['brands']) ? 5 - count($_SESSION['brands']) : 5
            ));
        }
    }

    public function blockResultForBrandById($brand_id){
        $brand = BrandMap::instance()->getById($brand_id);
        $this->blockResultForBrand($brand);

        return $this;
    }

    /**
     * Страница со списком тэгов
     *
     * @return DefaultController
     */
    public function blockTagList(){
        $tags = TagMap::instance()->listCloud();
        $this->view->cloudTags($tags);
        return $this;
    }

    public function blockBrandListByTagId($tag_id){
        $brands = BrandTagMap::instance()->listByTagId($tag_id);
        $posts  = PostTagMap::instance()->listPostByTagId($tag_id);

        $this->view->showTag( TagMap::instance()->getById($tag_id) );
        $this->view->brandList($brands);
        $this->view->showPosts($posts);


        return $this;
    }

    /**
     * Страница результат для бренда
     * Отображается облако тегов + вариант 
     * ответа пользователя. Ответ пользователя может 
     * находится в сесссии или в базе данны
     *
     *
     * @param $brand_id
     * @return DefautlController
     */
    public function blockResultForBrand(IBrand $brand){
        $brand_id   = $brand->id;
        if (isset($_SESSION['brands'][$brand_id])){
            $tag    = TagMap::instance()->getTagByName($_SESSION['brands'][$brand_id]);
            $link   = BrandTagMap::instance()->getByLink($brand_id, $tag);
        }


        if (!$brand){
            PageException::pageNotFound();
        }

        if ($brand->status !== Status::APPROVE && !UserController::create()->isAdmin()){
            $this->addBlock('brandDeleting', $brand);
            return $this;
        }

        $this->addHtml('<table id="user_last_result">');
        $this->addHtml('<caption>Результат:</caption>');
        $this->addHtml('<tr><td style="width: 70px" class="brand-container"><div class="brand">');
        $this->view->brandShowLink($brand, $brand->title);
        $this->addHtml('</div></td><td style="text-align: left">');
        if (isset($link)){
            $this->addHtml(sprintf('Вы сказали что это <b>&bdquo;%s&rdquo;</b>.<br/>', htmlspecialchars($tag->name)));
            $this->addHtml(sprintf('%d человек ответили так же', $link->count - 1));
        }

        $this->addHtml('<hr />');

        $links  = BrandTagMap::instance()->listByBrandId($brand_id);
        foreach ($links as $link) {
            $this->blockShowTag($link);
        }

        $this->addHtml('</td></tr></table>');

        return $this;
    }

    public function blockShowTag(BrandTag $link){
        $tag = TagMap::instance()->getById($link->tag_id);
        $this->addHtml(sprintf('<b>%d</b>: %s<br />', $link->count, htmlspecialchars($tag->name)));
    }

    /**
     * Страница сливания тегов
     *
     * @param $tag_id
     * @return DefaultController
     */
    public function blockTagMerge($tag_id){
        $this->requestAdminAuthorize();

        $tag = TagMap::instance()->getById($tag_id);

        if (empty($_POST['merge_tags'])){

            $criteria = new BrandTagCriteria();
            $criteria->setExpectIds($tag_id);
            $criteria->setOrder('name+');
            $choise = TagMap::instance()->listByCriteria($criteria);

            $this->view->showTagMergeForm( $tag, $choise );
            return $this;
        }

        $master = TagMap::instance()->getById($_POST['tag']);
        try{
            $master->merge($tag);
        }
        catch (MysqlException $e){
            if ($e->getCode() === MysqlException::EXPECT_MODIFY){
                $this->addHtml('<h3>уже склеено?</h3>');
                return $this;
            }
            throw $e;
        }

        PageException::pageRedirect( '/tags/' . $master->id . '/' );
    }

    public function blockTagRemove($tag_id){
        $this->requestAdminAuthorize();

        if (isset($_POST['tag_remove']) && isset($_SESSION['tag_remove']) && md5($_SESSION['tag_remove']) === $_POST['tag_remove']){
            TagMap::instance()->getById($tag_id)->remove();
            PageException::pageRedirect('/tags/');
        }

        $_SESSION['tag_remove'] = date('Y-m-d') . $tag_id;
        $this->addHtml(sprintf('<form action="" method="post"><input type="hidden" name="tag_remove" value="%s" /><input type="submit" value="Точно?" />', md5($_SESSION['tag_remove']) ));

        return $this;
    }

    public function blockTagApprove($tag_id){
        $this->requestAdminAuthorize();

        TagMap::instance()->approve($tag_id);
        PageException::pageBackRedirect();
    }

    /**
     * @return DefaultController
     */
    static public function create(){
        $controller = new self();
        $template   = new BrandTemplate();

        $controller->setTemplate( $template );

        $controller->view = new Layout($template, $controller);

        return $controller;
    }
}

?>
