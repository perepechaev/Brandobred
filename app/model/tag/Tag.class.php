<?php

require_once 'app/model/tag/TagMerge.class.php';

class Tag extends ObjectDataComponent implements ITag
{
    public function make(){
        $this->field('id',          'int',      array('auto'));
        $this->field('name',        'string',   array('request', 'unique'));
        $this->field('count',       'int',      array('request', 'default' => 0)); // Количество связанных брендов
        $this->field('status',      'enum',     array(
            'request', 
            'default' => Status::APPROVE, 
            'values'=> Status::getAvailable()
        ));
                
        $this->alias('name', 'title');
        
        $this->name('tag');
    }
    
    public function increment(){
        Mysql::instance()->start_transaction();
        try{
            $this->reload();
            $this->count++;
            $this->save();
        }
        catch (Exception $e){
            Mysql::instance()->revert_transaction();
            throw $e;
        }
        
        Mysql::instance()->commit_transaction();
    }
    
    /**
     * Слить в текущий тэг ведомый
     * 
     * @return void
     */
    public function merge(Tag $slave){
        $mysql = Mysql::instance();
        $mysql->start_transaction();

        try {
            $this->count += $slave->count;
            $slave->count = 0;
            
            $this->save();
            $slave->save();
            
            $mysql->queryf('INSERT IGNORE INTO `tag_merge` (master_id, slave_id) VALUES (%d, %d)', array(
                $this->id, $slave->id
            ));
            
            $mysql->queryf('UPDATE IGNORE `brand_tag` SET `tag_id` = %d WHERE tag_id = %d', array(
                $this->id, $slave->id
            ));
            $mysql->queryf('DELETE FROM `brand_tag` WHERE `tag_id` = %d', $slave->id);
            
            $mysql->queryf('UPDATE `post_tag` SET `tag_id` = %d WHERE tag_id = %d', array(
                $this->id, $slave->id
            ));
            
            $sql = <<<SQL
            UPDATE `brand_tag` bt1 SET `count` = (
                SELECT count(*) 
                FROM `user_brand_tag`
                WHERE (
                    tag_id = bt1.tag_id 
                    OR tag_id IN (
                        SELECT slave_id 
                        FROM tag_merge WHERE master_id = bt1.tag_id
                    )
                ) 
                AND brand_id = bt1.brand_id
            ) 
            WHERE `tag_id` = %d
SQL;
            $mysql->queryf($sql, $this->id);
            $mysql->commit_transaction();
        }
        catch (Exception $e){
            $mysql->revert_transaction();
            throw $e;
        }
    }
    
    public function remove(){
        TagMap::instance()->remove($this->id);
    }
    
    public function __clone(){
        return MysqlData::__clone();
    }
    
    public function __get($name){
        return MysqlData::__get($name);
    }
    
    public function createList(){
        return new MysqlList();
    }
    
    public function getAccessor(){
        return TagMap::instance();
    }
    
    public function destroy(){
        return false;
    }
}

?>