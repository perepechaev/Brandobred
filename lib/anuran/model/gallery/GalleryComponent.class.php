<?php

require_once(PATH_CORE . '/Controller.class.php');
require_once(PATH_MODEL . '/gallery/GalleryDataComponent.class.php');

class GalleryComponent extends ObjectComponent
{
    static private $template        = null;

    static private $urls            = array(
        'theme'         => '/gallery/%u/',
        'themeEdit'     => '/gallery/%u.edit.html',
        'themeStore'    => '/gallery/%u.store.html',
        'themeDelete'   => '/gallery/%u.delete.html',
        'themeNew'      => '/gallery/new/',
        'gallery'       => '/gallery/%u/%u.html',
        'uploadImage'   => '/gallery/%u/new/',
        'storeImage'    => '/gallery/%u.storeimage.html',
        'editImage'     => '/gallery/%u/%u.edit.html',
        'deleteImage'   => '/gallery/%u/%u.delete.html',
    );

    const PATH_GALLERY = '/img/gallery/';

    public function prepareNewImage($themeId){
        $this->imgActionEdit($themeId, null);
    }

    static public function useTemplate(Template $t){
        self::$template = $t;
    }

    static public function makeThemeUrl($id){
        return sprintf(self::$urls['theme'], $id);
    }

    static public function makeGalleryUrl(GalleryDataComponent $gallery){
        return sprintf(self::$urls['gallery'], $gallery->theme_id, $gallery->id);
    }

    static public function makeUploadImageUrl(ThemeGalleryDataComponent $theme){
        return sprintf(self::$urls['uploadImage'], $theme->id);
    }

    static public function makeThumbUrl(GalleryDataComponent $gallery){
        return GalleryComponent::PATH_GALLERY . $gallery->theme_id . '/s' . $gallery->id . '.jpg';
    }

    static public function makeImageUrl(GalleryDataComponent $gallery){
        return GalleryComponent::PATH_GALLERY . $gallery->theme_id . '/l' . $gallery->id . '.jpg';
    }

    static public function makeImageEditUrl(GalleryDataComponent $image){
        return sprintf(self::$urls['editImage'], $image->theme_id, $image->id);
    }

    static public function makeImageStoreUrl(ThemeGalleryDataComponent $theme){
        return sprintf(self::$urls['storeImage'], $theme->id);
    }

    static public function makeImageDeleteUrl(GalleryDataComponent $image){
        return sprintf(self::$urls['deleteImage'], $image->theme_id, $image->id);
    }

    static public function makeThemeEditUrl(ThemeGalleryDataComponent $theme){
        return sprintf(self::$urls['themeEdit'], $theme->id);
    }

    static public function makeThemeStoreUrl(ThemeGalleryDataComponent $theme){
        return sprintf(self::$urls['themeStore'], $theme->id);
    }

    static public function makeThemeDeleteUrl(ThemeGalleryDataComponent $theme){
        return sprintf(self::$urls['themeDelete'], $theme->id);
    }

    static public function makeThemeNewUrl(){
        return self::$urls['themeNew'];
    }

    /**
     * @param   string  $action
     * @param   array   $param
     * @return  GalleryComponent
     */
    public function prepareAction($action, $param){
        $contr      = $this;
        $method     = 'action' . $action;
        assert(method_exists($contr, $method));

        call_user_func_array(array($contr, $method), $param);
        return $contr;
    }

    public function prepareImageAction($action, $themeId, $imgId){
        $contr      = $this;
        $method     = 'imgAction' . $action;
        assert(method_exists($contr, $method));

        call_user_func(array($contr, $method), $themeId, $imgId);
        return $contr;
    }

    static public function getControlImagePanel(GalleryDataComponent $image){

        require_once(PATH_TEMPLATE . '/article/ArticleTemplate.class.php');

        $tmp        = new ArticleTemplate();
        $links      = array(
            'list'  => array('link' => 'themeUrl'       , 'title' => '&larr; Вернуться к списку'),
            'edit'  => array('link' => 'editUrl'        , 'title' => 'Редактировать'),
            'upload'=> array('link' => 'uploadImgUrl'   , 'title' => 'Загрузить фото'),
            'delete'=> array('link' => 'deleteUrl'      , 'title' => 'Удалить'),
        );

        $param      = array('list');
        if (UserAuthorize::instance()->isAdmin() && $image->id){
            $param[]    = 'edit';
            $param[]    = 'delete';
         }

        if ($image->id){
            $items      = array();
            foreach ($param as $key){
                $field      = $links[$key]['link'];
                $items[]    = array(
                    'title'     => $links[$key]['title'],
                    'url'       => $image->{'get' . $field}()
                );
            }
        }
        else {
            $items  = array(array());
            $items[0]['url']    = 'JavaScript: window.history.back();';
            $items[0]['title']  = $links['list']['title'];
        }

        $result     = $tmp->get('drawControl', array(
            'list'      => $items,
        ));

        return $result;
    }


    /**
     * @return GalleryComponent
     */
    static public function create(){
        return new GalleryComponent();
    }
}

?>