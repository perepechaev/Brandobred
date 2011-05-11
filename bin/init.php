<?php

chdir(dirname(__FILE__) . '/../');
require_once 'config.php';
require_once PATH_CORE . '/assert.php';

require_once 'app/model/brand/Brand.class.php';
require_once 'app/model/user/User.class.php';
require_once 'app/model/user/UserOauth.class.php';
require_once 'app/model/user/UserBrandTag.class.php';
require_once 'app/model/user/UserPost.class.php';
require_once 'app/model/tag/Tag.class.php';
require_once 'app/model/tag/TagMerge.class.php';
require_once 'app/model/tag/TagCensure.class.php';
require_once 'app/model/user/PostTag.class.php';
require_once 'app/model/banner/Banner.class.php';

function _init_alter_table(MysqlData $data){
    $mysql = Mysql::instance();
    $mysql->createTable( $data );
    $mysql->alterTable( $data );
}

$mysql  = Mysql::instance();
$mysql->createDbIfNotExists();

$mysql->createTable( new Brand() );
$mysql->alterTable( new Brand() );

$mysql->createTable( new Tag() );
$mysql->alterTable( new Tag() );

$mysql->createTable( new BrandTag() );
$mysql->alterTable( new BrandTag() );

$mysql->createTable( new User() );
$mysql->alterTable( new User() );

$mysql->createTable( new UserOauth() );
$mysql->alterTable( new UserOauth() );

$mysql->createTable( new UserBrandTag() );
$mysql->alterTable( new UserBrandTag() );

_init_alter_table( new UserPost());
_init_alter_table( new PostTag());
_init_alter_table( new TagMerge());
_init_alter_table( new TagCensure());
_init_alter_table( new Banner());

$mysql->createTable( new BrandIndustry() );
$mysql->alterTable( new BrandIndustry() );
if (BrandIndustryMap::instance()->listAll(1, 'id')->count() == 0){
    $industries = array('Пиво', 'Водка', 'Молочные продукты', 'Мясопродукты',
        'Сигареты', 'Сок', 'Кондитерские изделия', 'Бытовая техника',
        'Продукты быстрого приготовления', 'Макароны, мука, крупы',
        'Растительное и сливочное масло, майонез', 'Бананы', 'Моторное масло',
        'Шины'
    );
    foreach ($industries as $title){
        $industry = new BrandIndustry();
        $industry->title = $title;
        $industry->save();
    }
}

/**
 * Пересчет tag.count:
   UPDATE tag SET `count` = (SELECT 
     COUNT(DISTINCT brand_id) 
     FROM brand_tag 
     LEFT JOIN brand ON (brand.id = brand_tag.brand_id) 
     WHERE tag_id = `tag`.id AND `brand`.`status` = 'approve'
   )
 */


$mysql->query("UPDATE `user` set role = 'admin' where id = 1");

?>
