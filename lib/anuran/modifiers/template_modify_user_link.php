<?php
function template_modify_user_link($user, $title = null){
    if (is_object($user)){
        $title = is_null($title) ? $user->name : $title;
        return "<span>" . 
            template_modify_href(
                template_modify_link('user', $user->login), 
                $title
            ) 
        . "</span>";
    }
    return "";
}
?>