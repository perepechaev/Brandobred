<?php
function template_modify_back_url(){
    return SiteSkeleton::instance()->getPage()->getUri();
}
?>