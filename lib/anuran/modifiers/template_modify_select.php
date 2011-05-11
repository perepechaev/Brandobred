<?php

require_once dirname(__FILE__) . '/template_modify_terms.php';

function template_modify_select($param, $select_id, $select_name, $terms_name = false, $terms_current_name = false){
    $terms_current_name = empty($terms_current_name) ? $terms_name : $terms_current_name; 
    
    $select_id = $select_id ? "id=\"$select_id\"" : ""; 
    $html    = "<select $select_id name=\"$select_name\">";
    foreach ($param['list'] as $item){
        
        $item_id     = is_object($item) ? $item->id : $item;
        $is_current  = $param['current'] == $item_id ;
        $title       = is_object($item) ? $item->title : template_modify_terms($item, $is_current ? $terms_current_name : $terms_name); 

        
        $html   .= "<option value=\"$item_id\"" . ( $is_current ? " selected=\"selected\"" : "") . ">";
        $html   .= htmlspecialchars($title);
        $html   .= "</option>";
    }
    $html   .= "</select>";
    return $html;
}

function tselect($items, $current_id, $html_select_id, $html_select_name, $terms_name = false, $terms_current_name = false){
    return template_modify_select(array('list' => $items, 'current' => $current_id), $html_select_id, $html_select_name, $terms_name, $terms_current_name);
}

function tselectn($items, $current_id, $html_select_id, $html_select_name, $terms_name = false, $terms_current_name = false){
    $list = array('');
    foreach ($items as $item){
        $list[] = $item;
    }
    return template_modify_select(array('list' => $list, 'current' => $current_id), $html_select_id, $html_select_name, $terms_name, $terms_current_name);
}

?>