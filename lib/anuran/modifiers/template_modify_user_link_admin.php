<?php
function template_modify_user_link_admin($user, $title = null){
    if (is_object($user)){
        $title = is_null($title) ? $user->name : $title;
        return "<span>" . 
            template_modify_href(
                template_modify_link('manager', 'users', $user->id), 
                $title
            ) 
        . "</span>";
    }
    return "";
}
?>