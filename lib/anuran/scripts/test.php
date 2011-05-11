<?php

require_once(dirname(__FILE__) . '/test/TestHead.php');

function run_test($directory, $test_name){
    $test_file  = "$directory/$test_name/Test$test_name.class.php";
    
    static $tested    = array();
    if (isset($tested[$test_name])) return false;
    $tested[$test_name] = true;
    
    if (!file_exists($test_file)) return false;

    $force  = TEST_FORCE_CLEAR_DB ? ' --force' : '';
    $config = ' --config=' . PATH_CONFIG;
    ob_start();
    system('php ' . $test_file . $force . $config);
    ob_end_flush();
}

function test_directory($directory){
    $d = dir($directory);   
    while (false !== ($entry = $d->read())) {
        if ( ($entry === '.') || ($entry === '..') || ($entry === '.svn')){
            continue;
        }
        
        if (is_dir($d->path . '/' .$entry)) {
            run_test($directory, $entry);
        }
    }
    $d->close();
}

if (TEST_FORCE_CLEAR_DB === true){
    Mysql::instance()->query('DROP DATABASE IF EXISTS ' . TEST_DBNAME);
    Mysql::instance()->query('CREATE DATABASE ' . TEST_DBNAME);
    Mysql::instance()->useDb();
    
    require_once(PATH_SCRIPTS . '/init.php');
}

run_test(dirname(__FILE__) . '/test/', 'Mysql');
test_directory(dirname(__FILE__) . '/test/');
test_directory(PATH_PAGE_TEST);

?>