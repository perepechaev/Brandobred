<?php

require_once 'app/model/brand/Brand.class.php';

class TagMap extends BrandMap
{
    public function getData(){
        return new Tag();
    }
    
    public function getCriteria(){
        return new BrandCriteria();
    }
    
    public function remove($tag_id){
        $mysql = Mysql::instance();

        try{
            $mysql->start_transaction();
            
            $mysql->queryf('UPDATE `tag` SET `status` = "disapprove" WHERE `id` = %d', $tag_id);
            $mysql->queryf('UPDATE `tag_censure` SET `status` = "disapprove" where `tag_id` = %d', $tag_id);
            $mysql->queryf('DELETE FROM `brand_tag` WHERE `tag_id` = %d', $tag_id);
            $mysql->queryf('DELETE FROM `user_brand_tag` WHERE `tag_id` = %d', $tag_id);
            
            $mysql->commit_transaction();
        }
        catch (Exception $e){
            $mysql->revert_transaction();
            throw $e;
        }
    }
    
    public function approve($tag_id){
        $mysql = Mysql::instance();
        $mysql->queryf('UPDATE `tag` SET `status` = "approve" WHERE `id` = %d', $tag_id);
        $mysql->queryf('UPDATE `tag_censure` SET `status` = "approve" where `tag_id` = %d', $tag_id);
    }
    
    
    /**
     * @param $name
     * @return Tag
     */
    public function getTagByName($name){
        try{
            return $this->getByTitle($name, false);
        }
        catch (MysqlException $e){
            if ($e->getCode() === MysqlException::EXPECT_ONE_RECORD){
                $tag = $this->getData();
                $tag->name = $name;
                $tag->save();
                return $tag;
            }
            throw $e;
        }
    }
    
    /**
     * @param $pager
     * @return MysqlList
     */
    public function listApproved(Pager $pager = null, $order = 'priority-'){
        $criteria = $this->getCriteria();
        $criteria->setStatus(Status::APPROVE);
        $criteria->setOrder($order);
        
        return $this->listByCriteria($criteria, $pager);
    }
    
    
    /**
     * @return TagMap
     */
    static public function instance(){
        return new self();
    }
}

?>