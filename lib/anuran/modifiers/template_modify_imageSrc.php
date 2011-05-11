<?php
function template_modify_imageSrc($data, $size = ""){
    $link   = $data->{'imgurl' . $size};
    
    return $link;
}
?>