<?php

error_reporting(E_ALL | E_STRICT);

// Active assert and make it quiet
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_QUIET_EVAL, 1);

// Create a handler function
function my_assert_handler($file, $line, $code, $msg = '')
{
    throw new Exception($msg, (int) $code);
}
function my_error_handler($errno, $errstr, $errfile, $errline)
{
    if (empty($GLOBALS['ERROR_SKIP']) ){
	   my_assert_handler($errfile, $errline, $errno, $errstr);
    }
}
// Set up the callback
assert_options(ASSERT_CALLBACK, 'my_assert_handler');
set_error_handler("my_error_handler", E_ALL | E_STRICT);

function equal($equal, $msg = "")
{
    if (!$equal)
    {
        throw new Exception($msg);
    }
    assert($equal);
}

/**
 * Отобразить содержимое переменной
 *
 * @param unknown_type $var
 */
function dump($var, $print_r = false, $color = '#EEEEEE')
{
    if (is_object($var) && $var instanceof MysqlData){
        dump($var->getRawValues(), $print_r, $color);
        return true;
    }
    
    if (is_object($var) && $var instanceof MysqlList){
        $list = array();
        foreach ($var as $data) {
            $list[] = $data->getRawValues();
        }
        return dump($list);
    }
    
    if (defined('TESTING_RUN') && TESTING_RUN === true){
        ob_start();
        ($print_r === false) ? print_r($var) : var_dump($var);
        $output = ob_get_clean();
        $output = mb_convert_encoding($output, TEST_OUTPUT_ENCODE, 'UTF-8');
        echo $output;
        return null;
    }
    
    if (is_array($var) || is_object($var))
    {
        if (!defined('STDIN')){
            echo "<pre style=\"background-color: $color; border: 1px solid black; font-size: 12px; font-family: arial; padding: 5px; margin: 10px; text-align: left;\">";
        }
        ob_start();
        ($print_r) ? var_dump($var) : print_r($var);
        $content = ob_get_clean();
        if (!defined('STDIN')){
            echo htmlspecialchars($content);
            echo "</pre>";
        }
        else{
            echo $content;
        }
    }
    else
    {
        if (!defined('STDIN')){
            echo "<div style=\"background-color: $color; border: 1px solid black; font-size: 12px; font-family: arial; padding: 5px; margin: 10px; text-align: left;\">";
        }
        ob_start();
        ($print_r) ? print_r($var) : var_dump($var);
        $content = ob_get_clean();
        if (!defined('STDIN')){
            echo nl2br(htmlspecialchars($content));
            echo "</div>";
        }
        else{
            echo $content . "\n";
        }
    }
}

function dd(){
    $vars = func_get_args();
    foreach ($vars as $var) {
    	call_user_func('dump', $var);
    }
    die;
}

function dump_text($text, $color = '#EEEEEE'){
    echo "<pre style=\"background-color: $color; border: 1px solid black; font-size: 12px; padding: 5px; margin: 10px;\">";
    print_r(htmlspecialchars($text));
    echo "</pre>";
}

function __autoload($className){
    if (is_file($fileName = PATH_CORE . '/' . $className . '.class.php')){
        require_once($fileName);
    }

    if ( (strtolower(substr($className, 0, 4)) === 'page') && is_file($fileName = PATH_PAGE . '/' . strtolower(substr($className, 4)) . '.php')){
        require_once($fileName);
    }
}

function exception_log(Exception $e){
    if (defined('DEBUG_SHOW_EXCEPTION') && DEBUG_SHOW_EXCEPTION == true){
//        dump(get_class(Router::instance()->getController()) . '->' . Router::instance()->getMethod() . '("' . Router::instance()->getParams() .'")', false, 'yellow');
        dump('Ошибка ' . get_class($e)."({$e->getCode()}): ". $e->getMessage());
        dump($e->getTraceAsString());
    }
    
    $classname = get_class($e);
    $filename  = $classname . '.log';
    $date      = date('Y-m-d H:i:s');
    
    $session   = isset($_SESSION) ? print_r($_SESSION, true) : 'NULL';
    $get       = isset($_GET) ? print_r($_GET, true) : 'NULL';
    $post      = isset($_POST) ? print_r($_POST, true) : 'NULL';
    $server    = isset($_SERVER) ? print_r($_SERVER, true) : 'NULL';
    $cookie    = isset($_COOKIE) ? print_r($_COOKIE, true) : 'NULL';
    
//    dump('Ошибка ' . get_class($e)."({$e->getCode()}): ". $e->getMessage());
//    dump($e->getTraceAsString());
    
    $message  = <<<TEXT
\n
====== {$date} {$classname}({$e->getCode()}) ======
Message: {$e->getMessage()}
{$e->getTraceAsString()}

=== Session ===
$session

=== Cookie ===
$cookie

=== Server ===
$server

=== Get ===
$get

=== Post ===
$post

====== end of {$classname} =========================
\n
\n
\n
TEXT;

    file_put_contents(PATH_LOG . '/' . $filename, $message, FILE_APPEND);
}

function post_log($filename, $post = null){
    $date      = date('Y-m-d H:i:s');
    $post      = print_r($post, true);
    
    $message  = <<<TEXT
{$date}\t{$_SERVER['REMOTE_ADDR']}\t{$_SERVER['REQUEST_URI']}
$post

TEXT;
    file_put_contents(PATH_LOG . '/' . $filename, $message, FILE_APPEND);        
}

function pay_md5_log($filename, $mysum, $serversum){
    $date      = date('Y-m-d H:i:s');
    
    $message  = <<<TEXT
{$date}\t{$_SERVER['REMOTE_ADDR']}\t{$_SERVER['REQUEST_URI']}

mysum:{$mysum}\n
serversum:{$serversum}
TEXT;
    file_put_contents(PATH_LOG . '/' . $filename, $message, FILE_APPEND);        
    
}

function load_data_from_post(MysqlData $data){
    $args = func_get_args();
    unset($args[0]);
    foreach ($args as $name){
        $data->$name = $_POST[$name];
    }
}

function html($string){
    return nl2br(htmlspecialchars(trim($string)));
}

function _get($name, $default = ''){
    return isset($_GET[$name]) ? $_GET[$name] : $default;
}

function _post($name, $default = ''){
    return isset($_POST[$name]) && $_POST[$name] ? $_POST[$name] : $default;
}

/**
 * fix for php5.2.12 where not available method DateTime::diff()
 * 
 * importand! This function does not consider the number of days
 * 
 * please use the DateTime::diff() if your have version php 5.3.0 or later
 * 
 * @param $start
 * @param $end
 * @return unknown_type
 */
function _date_diff($start, $end){
    $end  = $end ? $end : date();
    
    $start = ($start instanceof DateTime) ? $start->format('Y-m-d H:i:s') : $start;
    $end   = ($end instanceof DateTime) ? $end->format('Y-m-d H:i:s') : $end;
    
    $inv   = $start > $end;
    $max   = max($start, $end);
    $start = min($start, $end);
    $end   = $max;
    
    $diff = strtotime($end) - strtotime($start);
    
    $interval = array();
    
    $interval['y'] = substr($end,0,4)  - substr($start,0,4);
    $interval['m'] = substr($end,5,2)  - substr($start,5,2);
    $interval['d'] = substr($end,8,2)  - substr($start,8,2);
    $interval['h'] = substr($end,11,2) - substr($start,11,2);
    $interval['i'] = substr($end,14,2) - substr($start,14,2);
    $interval['s'] = substr($end,17,2) - substr($start,17,2);
    $interval['invert'] = $inv;
    
    // 1. При переходе с летнего времени на зимнее и наоборот возможны чудеса
    // 2. PHP5.3.2 вылетает с Fatal error при попытке изменить поле DateInterval::days
    // $interval['days'] = floor((strtotime($end) - strtotime($start)) / 86400);
    
    _date_diff_normalize($interval, substr($start,0,4), substr($start,5,2));
    
    $result = new DateInterval('P0Y');
    foreach ($interval as $key => $value){
        $result->{$key} = $value;
    }
    
    return $result;
}

function _date_range(&$low_order, &$high_bit, $occurrences){
    if ($low_order < 0){
        $high_bit--;
        $low_order += $occurrences;
    }
}

function _date_diff_normalize(&$interval, $base_year, $base_month){
    _date_range($interval['s'], $interval['i'], 60);
    _date_range($interval['i'], $interval['h'], 60);
    _date_range($interval['h'], $interval['d'], 24);
    _date_range($interval['m'], $interval['y'], 12);
    
    _date_range($interval['d'], $interval['m'], _timelib_get_count_of_days_in_month($base_year, $base_month));
}

function _timelib_is_leap($year){
    return ($year % 4 == 0) && ( ($year % 100 != 0)  ||  ($year % 400 == 0));
}

function _timelib_get_count_of_days_in_month($year, $month){
    if (_timelib_is_leap($year)){
        $days = array(31,  31,  29,  31,  30,  31,  30,  31,  31,  30,  31,  30,  31);
    }
    else{
        $days = array(31,  31,  28,  31,  30,  31,  30,  31,  31,  30,  31,  30,  31);
    }
    
    return $days[(int) $month];
}

function formatDateDiff($start, $end=null) {
    if (in_array('0000-00-00 00:00:00', array($start, $end))){
    	return $start . " - " . $end;
    } 
    
    if(!($start instanceof DateTime)) { 
        $start = new DateTime($start); 
    } 
    
    if($end === null) { 
        $end = new DateTime(); 
    } 
    
    if(!($end instanceof DateTime)) { 
        $end = new DateTime($end); 
    }

    return _dateDiffTerms(_date_diff($start, $end));
    
    /* For debug mode :)
    echo ('<div style="font-family: verdana; background: #8EFFD0; margin: 0.5em 0.5em; padding: 0.3em 0.5em; border: 1px solid black;">' . $start->format('Y-m-d H:i:s') . ' &rarr; ' . $end->format('Y-m-d H:i:s'));
    $interval1 = date_diff($start, $end);
    $interval2 = _date_diff($start, $end);
    foreach ($interval2 as $key => $value){
        if ($value != $interval1->{$key} && $key != 'days'){
            dump($interval1);
            dump($interval2);
        }
    }
    echo "</div>";
    */
} 

/* boundary conditions for _date_diff();
echo formatDateDiff('2011-03-27 01:59:59', '2011-03-27 03:00:01') . "<br />";
echo formatDateDiff('2011-03-27 01:59:59', '2011-03-28 01:59:59') . "<br />";
echo formatDateDiff('1950-02-14', '2010-05-10') . "<br />";
echo formatDateDiff('2010-03-28 00:00:00', '2010-03-29 00:59:59') . "<br />";
echo formatDateDiff('2010-06-13', '2010-10-12') . "<br />";
echo formatDateDiff('2010-01-11 00:00:02', '2009-12-31 00:00:01') . "<br />";
echo formatDateDiff('2010-01-11 00:00:02', '2010-01-11 23:59:59') . "<br />";
echo formatDateDiff('2010-01-11 00:00:02', '2010-01-12 00:00:01') . "<br />";
echo formatDateDiff('2010-01-11 00:00:02', '2010-01-12 00:00:03') . "<br />";
die;
*/

function _dateDiffTerms(DateInterval $interval){
    $format = array(); 
    if($interval->y !== 0) { 
        return $interval->y . ' ' . template_modify_numeric($interval->y, 'год', "года", "лет"); 
    } 
    if($interval->m !== 0) { 
        return $interval->m . ' ' . template_modify_numeric($interval->m, 'месяц', "месяца", "месяцев"); 
    } 
    if($interval->d !== 0) { 
        return $interval->d . ' ' . template_modify_numeric($interval->d, 'день', "дня", "дней"); 
    } 
    if($interval->h !== 0) {
        return $interval->h . ' ' . template_modify_numeric($interval->h, 'час', "часа", "часов"); 
    } 
    if($interval->i !== 0) {
        return $interval->i . ' ' . template_modify_numeric($interval->i, 'минуту', "минуты", "минут"); 
    }
    if ($interval->s !== 0) {
        return $interval->s . ' ' . template_modify_numeric($interval->s, 'секунду', "секунды", "секунд");
    }
    
    return 'мгновение';
}

/**
 * Fix magic quote
 */
if (get_magic_quotes_gpc()) {
    function stripslashes_gpc(&$value)
    {
        $value = stripslashes($value);
    }
    array_walk_recursive($_GET, 'stripslashes_gpc');
    array_walk_recursive($_POST, 'stripslashes_gpc');
    array_walk_recursive($_COOKIE, 'stripslashes_gpc');
    array_walk_recursive($_REQUEST, 'stripslashes_gpc');
}

?>