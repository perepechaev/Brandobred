<?php
function template_modify_wiki_clear($text){

    return preg_replace('/\[{2}[\w\s\(\)\,]+\]{2}/', '', $text);
}

?>