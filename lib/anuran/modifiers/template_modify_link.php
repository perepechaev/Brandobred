<?php
function template_modify_link(){
    
    $params    = func_get_args();
    
    if (func_num_args()){
        $field     = $params[0];
        if (is_object($field)){
            unset($params[0]);
        }
    }
    
    foreach ($params as &$param){
        if ($param === '{field}'){
            unset($params[0]);
            $param = $field;
        }
        
        if ($param === '{back}'){
            return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_SERVER['REQUEST_URI'];
        }
        
        if (is_object($field) && !is_object($param) && preg_match('/\{(\w+)\}/', $param, $field_name)){
            $param = $field->{$field_name[1]};
        }
    }
    
    if (empty($params[0])){
        unset($params[0]);
    }
    
    $url       = Router::instance()->makeUrl($params);
    
    return ($url);
}

function tlink(){
    $params = func_get_args();
    return call_user_func_array('template_modify_link', $params);
}

function alink($title, $url){
    return sprintf('<a href="%s">%s</a>', $url, $title);
}

?>