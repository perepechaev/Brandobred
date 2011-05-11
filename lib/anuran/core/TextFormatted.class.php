<?php

class TextFormatted
{
    static public function cutText($text, $length, $suffix = '...'){
        $text   = strip_tags($text);
        if (mb_strlen($text, 'UTF-8') <= $length){
            return $text;
        }
        $pos    = mb_strpos($text, ' ', $length, 'UTF-8');
        $cut    = mb_substr($text, 0, $pos, 'UTF-8');
        if (mb_strlen($cut) === 0){
            return $text;
        }
        else {
            return  $cut. $suffix;
        }
    }
}

?>