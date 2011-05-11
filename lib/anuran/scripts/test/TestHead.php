<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

define('TESTING_RUN', true);

require_once('config.php');
$_SERVER['HTTP_HOST']   = 'http://' . URL_SITE;

require_once(PATH_CORE . '/assert.php');
require_once(PATH_CORE . '/Test.class.php');
require_once(PATH_CORE . '/SiteSkeleton.class.php');
require_once(PATH_TEST . '/Mail/TestMailUtils.php');
//require_once(PATH_PAGE_ETC. '/terms.php');
require_once(dirname(__FILE__) . '/TestCookie.class.php');

class PageTest extends Page
{
    public function header(){}
    public function footer(){}
	public function initialization(){}
	public function redirect(){}
}

if (!function_exists('fileupload_mime_allowed')){
function fileupload_mime_allowed(){
    return array(
        'text/plain',
    );
}}

if (!function_exists('fileupload_mime_disalowed')){
function fileupload_mime_disalowed(){
    return array(
    );
}}

function test_oncomplete($test_name){
    $text       = str_pad('', 10, '=') . " $test_name ";
    $text       = str_pad($text, 60, '=');
    echo "\n" . $text . "\n";
}

function test_onflush(){
    echo "\n";
}

$server     = array(
    'REMOTE_ADDR'   => '0.0.0.0'
);
$SESSION    = array();

SiteSkeleton::instance()->setServer($server);
SiteSkeleton::instance()->setPage( new PageTest() );
SiteSkeleton::instance()->setSession($SESSION);
SiteSkeleton::instance()->setMail(new TestMailUtils( ));

Test::setHandler('oncomplete', 'test_oncomplete');
Test::setHandler('onflush', 'test_onflush');

$force = false;
if (in_array('--force', $argv)){
    $force = true;
}
defined('TEST_FORCE_CLEAR_DB') or define('TEST_FORCE_CLEAR_DB', $force);

if (TEST_FORCE_CLEAR_DB === false){
    Mysql::instance()->query('DROP DATABASE IF EXISTS ' . TEST_DBNAME);
    Mysql::instance()->query('CREATE DATABASE ' . TEST_DBNAME);
    Mysql::instance()->useDb();
    
    ob_start();
    require_once(PATH_SCRIPTS . '/init.php');
    ob_end_clean();
}

function test_initialization_router(){
    static $isLoad  = false;
    
    if ($isLoad) return false;
    // Инициализация роутера
    Router::setPathClient( PATH_PAGE_ETC );
    Router::setUrl( URL_PATH );
    Router::instance('AuctionRouter');
    
    SiteSkeleton::instance()->setCookie( TestCookie::create() );
    
    $isLoad = true;
}

?>
