<?php

function template_modify_html_allow($var){
    $allows = func_get_args();
    unset($allows[0]);
    
    $amps = array(
        '&amp;bdquo;' => '&bdquo;',
        '&amp;rdquo;' => '&rdquo;',
        '&amp;mdash;' => '&mdash;',
    );
    $var = str_replace(array_keys($amps),array_values($amps), $var); 
    
    
    $amps = array();
    foreach ($allows as $allow){
        $amps[htmlspecialchars('<' . $allow . '>')] = '<' . $allow . '>'; 
        $amps[htmlspecialchars('<' . $allow . '/>')] = '<' . $allow . '/>'; 
        $amps[htmlspecialchars('</' . $allow . '>')] = '</' . $allow . '>'; 
    }
    
    return str_replace(array_keys($amps), array_values($amps), $var);
}

?>