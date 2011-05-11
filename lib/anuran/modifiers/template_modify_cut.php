<?php
function template_modify_cut($text, $length = 500){
    return TextFormatted::cutText($text, $length);
}
?>