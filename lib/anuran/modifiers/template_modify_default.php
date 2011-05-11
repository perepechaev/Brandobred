<?php
function template_modify_default($value, $default){
    return empty($value) ? $default : $value;
}
?>