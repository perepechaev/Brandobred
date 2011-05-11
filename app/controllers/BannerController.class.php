<?php

require_once 'app/controllers/DefaultController.class.php';
require_once 'app/model/banner/Banner.class.php';
require_once 'app/model/banner/BannerMap.class.php';
require_once 'app/model/banner/BannerTemplate.class.php';

class BannerController extends Controller
{
    
    /**
     * @var BannerTemplate
     */
    private $view;
    
    
    /**
     * Список баннеров
     * 
     * @return BannerController
     */
    public function blockBannerList(){
        UserSession::requestAdmin();
        
        $list = BannerMap::instance()->listAll(100, 'id');
        
        $this->view->controlBanner();
        $this->view->showBannerList($list);

        return $this;
    }
    
    public function blockBanner($banner_id){
        UserSession::requestAdmin();

        $this->blockBannerEdit($banner_id);
        
        return $this;
    }
    
    public function blockBannerEdit($banner_id = null){
        UserSession::requestAdmin();
        
        $this->view->controlBanner($banner_id);
        $this->blockBannerSave($banner_id);
        $this->blockBannerForm($banner_id);
        
        return $this;
    }
    
    public function blockBannerForm($banner_id = null){
        UserSession::requestAdmin();
        
        if ($banner_id) {
            $criteria = new BrandCriteria();
            $criteria->setData(new Banner());
            $criteria->setId($banner_id);
            $banner = Mysql::instance()->getByCriteria($criteria);
        }
        else {
            $banner = new Banner();
        }
        
        $this->view->showBannerForm( $banner );

        return $this;
    }
    
    private function blockBannerSave($banner_id = null){
        UserSession::requestAdmin();
        
        if ( isset( $_POST['banner_upload'] ) ){
            
            $file   = new FileUpload();
            $file->addAllowedType('swf');
            $file->setFile($_FILES, 'banner' );
            
            $filename = uniqid() . substr($_FILES['banner']['name'], strrpos($_FILES['banner']['name'], '.'));
            
            $banner = $banner_id ? BannerMap::instance()->getById($banner_id) : new Banner();
            
            if ($file->isTransfered()){
                $file->upload( PATH_PUBLIC . '/b', $filename);
                $banner->filename   = $filename;
            }
            
            if (!$file->isTransfered() && !$banner_id){
                $this->addHtml('<h3>Баннер не был загружен на сервер</h3>');
                $this->blockBannerForm($banner_id);
                return $this;
            }
            
            if (empty($_POST['remote_url'])){
                $this->addHtml('<h3>Не указан адрес перехода (url)</h3>');
                $this->blockBannerForm($banner_id);
                return $this;
            }
            
            $banner->remote_url = $_POST['remote_url'];
            $banner->place      = $_POST['place'];
            
            $banner->save();
            
            PageException::pageBackRedirect();
        }
        return $this;
    }
    
    public function blockBannerRemove($banner_id){
        UserSession::requestAdmin();
        
        Mysql::instance()->delete( BannerMap::instance()->getById($banner_id) );
        
        PageException::pageRedirect('/banner/');
    }
    
    public function placeBottom(){
        $banner = BannerMap::instance()->getRandomByPlace(BannerPlace::BOTTOM);
        if ($banner){
            $banner->hits++;
            $banner->save();
            $this->view->placeBottom($banner);
        }
                
        return $this;
    }
    
    public function placeRight(){
        $banner = BannerMap::instance()->getRandomByPlace(BannerPlace::RIGHT);
        if ($banner){
            $banner->hits++;
            $banner->save();
            $this->view->placeRight($banner);
        }
                
        return $this;
    }
    
    public function blockBannerConversion($banner_id){
        $banner = BannerMap::instance()->getById($banner_id);
        $banner->conversion++;
        $banner->save();
        
        PageException::pageRedirect( $banner->remote_url );
    }
    
    
    /**
     * @return BannerController
     */
    static public function create(){
        $controller = new self();
        $template   = new BannerTemplate();
        $controller->setTemplate( $template );
        $controller->view = new Layout($template, $controller);
        $controller->addHtml(''); // fix for empty banner

        return $controller;
    }
    
}
?>