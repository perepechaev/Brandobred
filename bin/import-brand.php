<?php

chdir('../');
require_once 'config.php';
require_once PATH_CORE . '/assert.php';

require_once 'app/model/brand/Brand.class.php';

$brand      = new Brand();
$criteria   = new BrandCriteria();
$map        = new BrandMap();
if ($map->getCountByCriteria($criteria)){
    printf("Brand is alredy import\n");
//    exit();
}

Mysql::instance()->createTable($brand);

$ls = glob('public/img/brands/*.jpg');
foreach ($ls as $filename){
    $brand = new Brand();
    $brand->filename = basename($filename);
    Mysql::instance()->trySave($brand);
}

?>