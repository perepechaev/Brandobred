<?

require_once 'app/model/tag/TagCensure.class.php';

class TagCensureMap extends BrandTagMap
{
    public function getData(){
        return new TagCensure();
    }
    
    public function getCriteria(){
        return new BrandTagCriteria();
    }
    
    /**
     * @param $pager
     * @return MysqlList
     */
    public function listDirty(Pager $pager = null){
        $criteria = $this->getCriteria();
        $criteria->setStatus(Status::DIRTY);
        $criteria->setOrder('tag_id-');
        $criteria->join(new Tag(), 'tag_id', 'id', array('name', 'count'));
        
        return $this->listByCriteria($criteria, $pager);
    }
    
    /**
     * @param $tag_id
     * @return TagCensure
     */
    public function getByTagId($tag_id){
        $criteria = $this->getCriteria();
        $criteria->setTagId($tag_id);
        
        return $this->getByCriteria($criteria);
    }
    
    
    /**
     * @return TagCensureMap
     */
    static public function instance(){
        return new self();
    }
}

?>