<?php
function template_modify_back_method(){
    return Router::instance()->getMethod();
}
?>