<?php

class ThemeGalleryDataComponent extends MysqlData
{
    protected function make(){
        $this->field('id',          'int',      array('request', 'unsigned', 'auto'));
        $this->field('title',       'string',   array('request'));
        $this->field('date',        'date',     array('request'));
        $this->field('text',        'string',   array('request', 'length' => 65536));

        $this->name('gallery_theme');
    }
    
    public function expectModify($count){
        return false;
    }

    /* Методы доступа к объекту */

    /**
     * @return MysqlList
     */
    static public function listLast($limit = 10, $offset = 0)
    {
        $mysql  = Mysql::instance();
        $themes = new ThemeGalleryDataComponent();
        $mysql->select('ORDER BY `date` DESC LIMIT '.$offset.','.$limit, $themes);
        $mysql->fetch($themes, $result);

        return $result;
    }

    /**
     * @param   int     $themeId
     * @return ThemeGalleryDataComponent
     */
    public function prepareById($themeId){
        $mysql      = Mysql::instance();
        $this->id   = $themeId;
        $mysql->get($this, '', 'WHERE `id`=:id:');
        return $this;
    }


    /* Магические методы */
    
    public function __get($key){
        if (method_exists($this, 'get' . $key)){
            return $this->{'get' . $key}();
        }
        return parent::__get($key);
    }

    public function getTextShort(){
        return TextFormatted::cutText($this->text, 20);
    }

    public function getDateHuman(){
        return DateFormatted::humanDate($this->date);
    }

    public function getUrl(){
        if ($this->id){
            return GalleryComponent::makeThemeUrl($this->id);
        }
        else {
            return PageGallery::create()->makeUrl();
        }
    }

    public function getEditUrl(){
        return GalleryComponent::makeThemeEditUrl($this);
    }

    public function getStoreUrl(){
        return GalleryComponent::makeThemeStoreUrl($this);
    }

    public function getDeleteUrl(){
        return GalleryComponent::makeThemeDeleteUrl($this);
    }

    public function getUploadImgUrl(){
        return GalleryComponent::makeUploadImageUrl($this);
    }

    public function getStoreImageUrl(){
        return GalleryComponent::makeImageStoreUrl($this);
    }

    public function getControlHtml(){
        return GalleryController::create()->getControlPannel($this);
    }

    public function getHtmlSpecialText(){
        return htmlspecialchars($this->text);
    }

    public function getHtmlTitle(){
        return htmlspecialchars($this->title);
    }

    /**
     * @return MysqlList
     */
    public function createList(){
        return new MysqlList();
    }

    /**
     * @return ThemeGalleryDataComponent
     */
    static public function create(){
        return new ThemeGalleryDataComponent();
    }
}
?>