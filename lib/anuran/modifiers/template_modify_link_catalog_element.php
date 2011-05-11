<?php
function template_modify_link_catalog_element($element){
    return Router::instance()->makeUrl(array('catalog', $element->group->title, $element->title));
}
?>