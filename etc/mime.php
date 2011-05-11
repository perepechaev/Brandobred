<?php

function fileupload_mime_allowed(){
    return array(
        'image/pjpeg',
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
    );
}

function fileupload_mime_disalowed(){
    return array();
}


?>