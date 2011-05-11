<?php

require_once 'app/model/brand/BrandIndustry.class.php';

class Status{
    const DIRTY         = 'dirty';
    const APPROVE       = 'approve';
    const DISAPPROVE    = 'disapprove';
    const TRASH         = 'trash';
    
    static public function getAvailable(){
        return array(self::DIRTY, self::APPROVE, self::DISAPPROVE, self::TRASH);
    }
}

interface IBrand{
    
}

interface ITag{
    
}

class Brand extends MysqlData implements IBrand
{
    public function make(){
        $this->field('id',          'int',      array('auto'));
        $this->field('filename',    'string',   array('request', 'unique'));
        $this->field('title',       'string',   array('request'));
        $this->field('company',     'string');
        $this->field('priority',    'int',      array('request', 'default' => '50'));
        $this->field('industry_id', 'int');
        
        $this->field('status',      'enum',     array(
            'request', 
            'default' => Status::DIRTY, 
            'values'=> Status::getAvailable()
        ));
        
        $this->name('brand');
    }
    
    public function expectOneRecord($count){
        if ($count === 0){
            return false;
        }
        return parent::expectOneRecord($count);
    }
    
    public function createList(){
        return new BrandList();
    }
}

class BrandList extends MysqlList
{
    /**
     * @var Pager
     */
    public $pager;
    
    /**
     * @param Pager $pager
     */
    public function setPager(Pager $pager){
        $this->pager = $pager;
    }
}

class BrandTag extends MysqlData implements IBrand, ITag
{
    public function make(){
        $this->field('brand_id',  'int',      array('request'));
        $this->field('tag_id',    'string',   array('request'));
        $this->field('count',     'int',      array('request', 'default' => 0));
        
        $this->unique(array('brand_id', 'tag_id'));
        
        $this->name('brand_tag');
    }
    
    public function update(){
        Mysql::instance()->updateByKey($this, array(
            'brand_id'  => $this->brand_id,
            'tag_id'    => $this->tag_id
        ));
    }
}

require_once PATH_MODEL . '/object/ObjectCriteriaComponent.class.php';
class BrandCriteria extends ObjectCriteriaComponent
{
    private $searchTitle;
    
    private $status;
    
    private $minPriority;
    
    private $maxPriority;
    
    private $minCount;
    
    public function setStatus($status){
        $this->status = (array) $status;
    }
    
    public function getStatus(){
        return $this->status;
    }
    
    public function getExpectStatus(){
        return array(Status::TRASH);
    }
    
    public function setSearchTitle($title){
        $this->searchTitle = $title ? $title : null;
    }
    
    public function getSearchTitle(){
        return $this->searchTitle;
    }
    
    public function setMinPrioriry($priority){
        $this->minPriority = $priority;
    }
    
    public function getMinPriority(){
        return $this->minPriority;
    }
    
    public function setMaxPrioriry($priority){
        $this->maxPriority = $priority;
    }
    
    public function getMaxPriority(){
        return $this->maxPriority;
    }
    
    public function setMinCount($count){
        $this->minCount = $count;
    }
    
    public function getMinCount(){
        return $this->minCount;
    }
    
    public function build(){
        $this->where( 
            $this->isLike('title', $this->getSearchTitle(), '%{like}%'),
            $this->isGreater('priority', $this->getMinPriority()),
            $this->isLetter('priority', $this->getMaxPriority()),
            $this->isGreater('count', $this->getMinCount(), true)
        );
        
        parent::build();
    }
}

require_once PATH_MODEL . '/object/ObjectAccessorComponent.class.php';
class BrandMap extends ObjectAccessorComponent
{
    public function getData(){
        return new Brand();
    }
    
    /* 
     * 
     * @return BrandCriteria
     */
    public function getCriteria(){
        return new BrandCriteria();
    }
    
    public function getRandExceptIds($ids = array()){
        $criteria   = new BrandCriteria();
        
        $criteria->setData($this->getData());
        
        $perc = ceil(log(rand(1, 1000), 2) * 10);
        
        $criteria->setOrder('priority+', 'RAND()');
        $criteria->setMinPrioriry( $perc );
        $criteria->setPage(1);
        $criteria->setPageCount(1);
        $criteria->setExpectIds($ids);
        $criteria->setStatus(Status::APPROVE);
        
        $brand = $this->getMysql()->getByCriteria($criteria);
        if (!$brand){
            $criteria->setMinPrioriry(null);
            $criteria->setOrder('priority-', 'RAND()');
            $criteria->setMaxPrioriry( $perc );
            $brand = $this->getMysql()->getByCriteria($criteria);
        }
        
        return $brand;
    }
    
    public function getRand(){
        $data       = $this->getData();
        $criteria   = $this->getCriteria();
        
        $criteria->setData($data);
        $criteria->setOrder('RAND()');
        $criteria->setPage(1);
        $criteria->setPageCount(1);
        $criteria->setStatus( Status::APPROVE );
        
        return $this->getMysql()->getByCriteria($criteria);
    }
    
    
    /**
     * @param $pager
     * @return MysqlList
     */
    public function listApproved(Pager $pager = null, $order = 'priority-'){
        $criteria = $this->getCriteria();
        $criteria->setStatus(Status::APPROVE);
        $criteria->join(new BrandTag(), 'id', 'brand_id', array('count(tag_id) as count'));
        $criteria->setOrder($order, 'id-');
        $criteria->setCrtieraGroup('id');
        
        return $this->listByCriteria($criteria, $pager);
    }
    
    public function listCloud(){
        $criteria = $this->getCriteria();
        
        $criteria->setStatus(Status::APPROVE);
        $criteria->setOrder('name+', 'id-');
        $criteria->setMinCount(3);
        
        return $this->listByCriteria($criteria);
    }
    
    /** 
     * Получить список брендов за которые не голосовал пользователь, но которые
     * присутствуют в массиве $brands_id
     * 
     * @param $user_id      int
     * @param $brand_ids    array
     * @return MysqlList
     */
    public function listByUserIdIn($user_id, $brand_ids){
        /*
         select * from brand 
         left join user_brand_tag ON (brand.id = user_brand_tag.brand_id AND user_brand_tag.user_id = 1) 
         where brand.id in (1, 2, 3, 4, 5, 6) 
         and tag_id IS NULL
        */
        /*
        $condition = new MysqlCondition( new UserBrandTag() );
        
        $criteria = $this->getCriteria();
        $criteria->setData( $this->getData() );
        $criteria->setIds( $brand_ids );
        $criteria->join( 
            new UserBrandTag(), 
            'id', array('brand_id', $condition->equal('user_id', $user_id)), 
            array('user_id'),
            $condition->isNull('tag_id', true)
        );
        */
        $user_id = (int) $user_id;
        $brands  = '(' . implode(',', array_map('intval', $brand_ids)) . ')';
        
        $query = <<<SQL
            SELECT * FROM brand 
            LEFT JOIN `user_brand_tag` ON 
                (`brand`.`id` = `user_brand_tag`.`brand_id` AND `user_brand_tag`.`user_id` = $user_id) 
            WHERE `brand`.`id` in $brands 
            AND `tag_id` IS NULL
SQL;
        
        Mysql::instance()->query($query);
        return Mysql::instance()->fetch(new Brand());
    }
    
    
    public function listWithCountTag(){
        // select id, title, sum(`count`) as count from brand left join brand_tag on (id = brand_id) group by id
        
        $data       = $this->getData();
        $criteria   = $this->getCriteria();
        
        $criteria->setData($data);
        $criteria->join( new BrandTag(), 'id', 'brand_id', array('sum(count) as count_tag'));
        $criteria->setCrtieraGroup('id');
        $criteria->setExpectStatus(null);
        $criteria->setStatus(Status::APPROVE);
        
        return $this->getMysql()->listByCriteria($criteria);
    }
    
    
    /**
     * @return BrandMap
     */
    static public function instance(){
        return new self();
    }
}

class BrandTagMap extends ObjectAccessorComponent
{
    public function getData(){
        return new BrandTag();
    }
    
    /* (non-PHPdoc)
     * @see lib/anuran/model/object/ObjectAccessorComponent#getCriteria()
     * @return BrandTagCriteria
     */
    public function getCriteria(){
        return new BrandTagCriteria();
    }
    
    public function listByBrandId($brand_id, $sort = 'count-', $limit = 15){
        $criteria = new BrandTagCriteria();
        $criteria->setData($this->getData());
        $criteria->join( new Tag(), 'tag_id', 'id', array('name', 'id'));
        $criteria->setBrandId($brand_id);
        $criteria->setOrder($sort);
        
        $links = Mysql::instance()->listByCriteria($criteria);
        return $links;
    }
    
    public function listByTagId($tag_id, $sort = 'count-', $status = Status::APPROVE){
        $condition = new MysqlCondition( new Brand() );
        
        $criteria = new BrandTagCriteria();
        $criteria->setData($this->getData());
        $criteria->setStatus($status);
        $criteria->join( new Brand(), 'brand_id', 'id', array('*'),
            $condition->isIn('status', $criteria->getStatus())
        );
        $criteria->setTagId($tag_id);
        $criteria->setOrder($sort);
        
        return Mysql::instance()->listByCriteria($criteria);
    }
    
    public function getByLink( $brand_id, Tag $tag){
        $tag_id   = $tag->id;
        
        $criteria = new BrandTagCriteria();
        $criteria->setData($this->getData());
        $criteria->setTagId($tag_id);
        $criteria->setBrandId($brand_id);
        try {
            $link = Mysql::instance()->getByCriteria($criteria);
            return $link;
        }
        catch (MysqlException $e){
            if ($e->getCode() === MysqlException::EXPECT_ONE_RECORD){
                $link = new BrandTag();
                $link->brand_id = $brand_id;
                $link->tag_id   = $tag_id;
                $link->count    = 0;
                $link->save();
                
                $tag->increment();
                
                return $link; 
            }
            throw $e;
        }
    }
    
    /**
     * @return BrandTagMap
     */
    static public function instance(){
        return new self();
    }
}

class BrandTagCriteria extends BrandCriteria
{
    private $brandId;
    
    private $tagId;
    
    public function setBrandId($id){
        $this->brandId = $id;
    }
    
    public function getBrandId(){
        return $this->brandId;
    }
    
    public function setTagId($id){
        $this->tagId = $id;
    }
    
    public function getTagId(){
        return $this->tagId;
    }
    
    public function build(){
        $this->where(
            $this->isEqual('brand_id', $this->getBrandId()),
            $this->isEqual('tag_id', $this->getTagId()),
            $this->isIn('status', $this->getStatus()),
            $this->isIn('status', $this->getExpectStatus(), true)
        );
        
        $this->limit( $this->getPage(), $this->getPageCount() );
    }
    
    /**
     * @return BrandTagCriteria
     */
    static public function create(){
        return new self();
    }
}

/**
 * Поиск брендов по названию или тэгу
 *
 */
class BrandTagComposeCriteria extends BrandTagCriteria
{
    public function build(){
        $tag       = new MysqlCondition(new Tag());
        $brand     = new MysqlCondition(new Brand());

        $this->setData(  new BrandTag() );
        $this->join( new Brand(), 'brand_id', 'id', array('filename', 'title', 'status', 'id', 'priority') );
        $this->join( new Tag(), 'tag_id', 'id', 'name');
        
        $this->where(
            $this->expOr(
                $brand->isLike('title', $this->getSearchTitle()),
                $tag->isLike('title', $this->getSearchTitle())
            ),
            $brand->isIn('status', $this->getStatus())
        );
        
        $this->setCrtieraGroup('brand_id');
    }
    
}

?>