<?php
function template_modify_image($data, $size = ""){
    if (!is_object($data)){
        return '';
    }
    
    if ($data instanceof ImageData){
        return template_modify_image_object($data, $size);
    }
    
    $param  = $data->{"imgparam" . $size};
    $width  = isset($param['width']) && is_int($param['width']) ? " width=\"{$param['width']}\"" : "";
    $height = isset($param['height']) && is_int($param['height'])  ? " height=\"{$param['height']}\"" : "";
    $link   = $data->{'imgurl' . $size};

    $title  = $data->is_field('title') ? $data->title : '';
    
    return "<img src=\"$link\" $width $height class=\"mini\" alt=\"$title\" />";
}

function template_modify_image_object(ImageData $data, $size = 'default'){
    $image_id   = $data->id;
    $url        = $data->url($size);
    $title      = $data->title;
    
    return "<img src=\"" . $url . "\" class=\"{$size}\" alt=\"$title\" width=\"{$data->getWidth($size)}\" height=\"{$data->getHeight($size)}\" />";
}
?>