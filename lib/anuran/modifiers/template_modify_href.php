<?php
function template_modify_href($url, $title){
    $current_url = Router::instance()->getUrl();
    $class    = '';
    if ($current_url === $url || (substr($current_url, 0, strrpos('?', $current_url)) === $url)){
        $class    = ' class="current"';
    }
    return '<a href="' . $url . '" ' . $class. '>' . $title . '</a>';
}

function thref($url, $title){
    return template_modify_href($url, $title);
}

function ahref($title){
    $params = func_get_args();
    unset($params[0]);
    
    $href = call_user_func_array('template_modify_link', $params);

    return template_modify_href($href, $title);
}

function isActive($url){
    $current_url = Router::instance()->getUrl();
    return ($current_url === $url || (substr($current_url, 0, strrpos('?', $current_url)) === $url));
}

function hactive($first, $class){
    $current_url = Router::instance()->getUrl();
    
    $is_active = false;
    $is_active = strpos($current_url, $first) === 0;
    
    if ($first === '/' && $current_url !== '/'){
        $is_active = false;
    }
    
    
    return $is_active ? ' class="' . $class . '"' : '';
}

?>