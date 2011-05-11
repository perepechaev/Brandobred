<?php
function template_modify_php($value, $function){
    equal(function_exists($function), "Функция '$function()' не найдена");
    $params    = func_get_args();
    unset($params[1]);
    unset($params[0]);
    foreach ($params as &$param){
        if ($param === '{field}') $param = $value;
    }
    if (count($params) === 0) $params[] = $value;
    return call_user_func_array($function, $params);
}
?>