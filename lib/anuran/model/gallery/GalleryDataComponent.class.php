<?php

require_once(PATH_CORE . '/Mysql.class.php');
require_once(PATH_CORE . '/MysqlData.class.php');

require_once(dirname(__FILE__). '/ThemeGalleryDataComponent.class.php');

class GalleryDataComponent extends MysqlData
{
    public $url;
    public $thumbWidth = 150;
    public $thumbHeight = 150;
    public $thumbUrl;
    public $imageUrl;
    
    protected function make(){
        $this->field('id',          'int',      array('request', 'unsigned', 'auto'));
        $this->field('title',       'string');
        $this->field('date',        'date',     array('request'));
        $this->field('theme_id',    'int',      array('request', 'unsigned'));

        $this->name('gallery');
    }
    
    public function expectModify($count){
        return false;
    }
    
    public function onload(){
        $this->url = Router::instance()->makeUrl(array('gallery', $this->theme_id, $this->id));
        $this->thumbUrl = GalleryComponent::makeThumbUrl($this);
        $this->imageUrl = GalleryComponent::makeImageUrl($this);
    }
    
    public function __get($key){
        if (method_exists($this, 'get' . $key)){
            return $this->{'get' . $key}();
        }
        return parent::__get($key);
    }

    /* Методы доступа к объекту */

    static public function listLastByTheme($parentId, $limit = 50)
    {
        $mysql          = Mysql::instance();

        $glr            = new GalleryDataComponent();
        $glr->theme_id  = $parentId;

        $mysql->select('WHERE `theme_id`=:theme_id: ORDER BY `date` DESC LIMIT '.(int)$limit, $glr);
        $mysql->fetch($glr, $result);
        return $result;
    }

    /**
     * @param   int $id
     * @return  GalleryDataComponent
     */
    static public function getById($id){
        $mysql          = Mysql::instance();
        $gallery        = new GalleryDataComponent();
        $gallery->id    = $id;
        $mysql->get($gallery, '', 'WHERE id=:id:');
        return $gallery;
    }

    function prepareLast($limit = false, $offset = false)
    {
        $extra  = " ORDER BY `date` DESC";
        $extra .= ($limit)  ? " LIMIT " . $limit : "";
        $extra .= ($offset) ? ","      . $offset : "";

        $this->select(array(), $extra);
    }

    function prepareByDate($date)
    {
        $param  = array(
            'date='  => $date,
        );
        $this->select($param);
    }

    function prepareByTheme($theme_id, $limit = 0, $offset = 0)
    {
    	$param	= array(
    		'theme_id'	=> $theme_id
    	);
    	$extra	= ($limit != 0) ? " LIMIT $offset,$limit" : "";
    	$this->select($param, $extra);
    	return $this;
    }

    function countByTheme($theme_id)
    {
    	$param	= array(
    		'theme_id'	=> $theme_id
    	);

    	return Gallery::create()->countByParam($param);
    }

    /* Магические методы */

    public function getDateHuman(){
        return DateFormatted::humanDate($this->date);
    }

    public function getUrl(){
        return GalleryComponent::makeGalleryUrl($this);
    }

    public function getImageUrl(){
        return GalleryComponent::makeImageUrl($this);
    }

    public function getEditUrl(){
        return GalleryComponent::makeImageEditUrl($this);
    }

    public function getDeleteUrl(){
        return GalleryComponent::makeImageDeleteUrl($this);
    }

    public function getThemeUrl(){
        return GalleryComponent::makeThemeUrl($this->theme_id);
    }

    public function getThemeTitle(){
        $theme  = ThemeGalleryDataComponent::create()->prepareById($this->theme_id);
        return $theme->title;
    }

    public function getHtmlTitle(){
        return htmlspecialchars($this->title);
    }

    public function getControlHtml(){
        return GalleryComponent::getControlImagePanel($this);
    }

    /**
     * @return MysqlList
     */
    public function createList(){
        return new MysqlList();
    }
}
?>