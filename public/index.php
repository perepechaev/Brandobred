<?php

// XXX: При неудачной загрузке удалить бренд из бд

chdir('../');
require_once 'lib/anuran/core/assert.php';
require_once 'config.php';
require_once 'lib/anuran/core/Controller.class.php';
require_once 'app/controllers/DefaultController.class.php';
require_once 'app/controllers/UserController.class.php';
require_once 'app/controllers/TwitterController.class.php';
require_once 'app/controllers/FacebookController.class.php';
require_once 'app/controllers/LivejournalController.class.php';
require_once 'app/controllers/BannerController.class.php';
require_once PATH_TEMPLATE_MODIFIERS . '/template_modify_numeric.php';

function content(){
    $router = Router::instance();
    call_user_func_array(array($router->getController(), $router->getMethod()), $router->getParams())->draw();
}

function _getDeltaTime()
{
  $nUXDate1 = strtotime(date('2010-03-16'));;
  $nUXDate2 = strtotime(date('Y-m-d'));

  $nUXDelta = $nUXDate1 - $nUXDate2;
  $strDeltaTime = "" . $nUXDelta/60/60/24; // sec -> hour
            
  $nPos = strpos($strDeltaTime, ".");
  if ($nPos !== false)
    $strDeltaTime = substr($strDeltaTime, 0, $nPos + 3);

  return max($strDeltaTime, 0);
}

/**
 * Terms
 * 
 * @param $value string входящее значение
 * @param $name  string имя terms
 * @return unknown_type
 */
function terms($value, $name){
    return $value;
}

try{
    ob_start();
    
    Session::auto();
    UserController::create()->validateAdminIp();
    
    require_once 'etc/BrandtagRouter.class.php';
    Router::setPathClient(PATH_ROUTER);
    Router::setUrl($_SERVER['REQUEST_URI']);
    Router::instance('BrandtagRouter');
    
    require_once PATH_TEMPLATE . '/wrapper.php';
    ob_end_flush();
}
catch (RouterException $e){
    ob_end_clean();
    if ($e->getCode() === RouterException::NOT_FOUND_METHOD){
        header('HTTP/1.0 404');
        include('app/view/page404.html');
        die;
    }
    exception_log($e);
}
catch (PageException $e){
    ob_end_clean();
    $e->handler();
    die;
}
catch (Exception $e){
    ob_end_clean();
    header("HTTP/1.0 500 Internal Server Error");
    
    exception_log($e);
}

?>