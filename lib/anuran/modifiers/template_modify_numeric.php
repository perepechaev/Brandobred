<?php
function template_modify_numeric($num, $one, $two, $many){
    $result = $many;
    
    if ($num > 20 || $num < 10) {
        $result = substr($num, -1) < 5 ? $two : $result;
        $result = substr($num, -1)  == 1 ? $one : $result;
        $result = substr($num, -1)  == 0 ? $many : $result;
    }
    return $result;
}
?>