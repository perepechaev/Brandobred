<?php
function template_modify_wiki($text, $isShowImage = 1){
    wiki_image($text, $isShowImage);
    wiki_href($text);
    wiki_link($text);

    return preg_replace('/\[{2}[\w\s\(\)\,]+\]{2}/', '', $text);
}

function wiki_image(&$text, $isShowImage){
    $text = preg_replace_callback('/\[{2}img\((\d+)\,(\w+)\,?(left|fleft|center|right|fright)?\)\]{2}/', $isShowImage ? 'wiki_image_replace' : 'wiki_image_cut', $text);
}

function wiki_image_replace($mathes){
    static $image;
    
    if (!isset($image)){
        require_once(PATH_PAGE_MODEL . '/image/ImageComponent.class.php');
        $image  = ImageComponent::create();
    }
    
    $image_id   = $mathes[1];
    $size       = $mathes[2];
    $position   = isset($mathes[3]) ? $mathes[3] : 'fleft';

    try {
        $im         = $image->getAccessor()->getById($image_id);
        equal($im instanceof ImageData, get_class($im));
        $url        = $im->url($size);
        $title      = $im->title;
    }
    catch (ImageException $e){
        if ($e->getCode() === ImageException::IMAGE_NOT_FOUND) return "<span style='color: red; font-size: 80%; padding-left: 2em; padding-right: 2em;'>изображение не найдено</span>";
        throw $e;
    }
    
    return "<img src=\"" . $url . "\" class=\"{$size} pos_{$position}\" alt=\"$title\" width=\"{$im->getWidth($size)}\" height=\"{$im->getHeight($size)}\" />";
}
function wiki_image_cut($mathces){
    return "<!-- wiki cut image -->";
}

function wiki_href(& $text, $callback = 'wiki_href_external_replace'){
    $text = preg_replace_callback('/http\:\/\/((([\wа-я\-]{2,}\.)+[\wа-я\-]+)(\/[\wа-я\-]*(\.\w{3,4})?)*(\?([\wа-я\-]*\=[\wа-я\-]*(&amp\;){0,1}&?)*)?)/ui', $callback, $text);
    $text = preg_replace_callback('/\[http\:((([\wа-я\-]{2,}\.)+[\wа-я\-]+)(\/[\wа-я\-]*(\.\w{3,4})?)*(\?[\w\&\;\=\-]*(()))?)\s([\wа-я\s\:\/\=\?&\;\.\-]+)\]/ui', $callback, $text);
    $text = preg_replace_callback('/\[http\:((([\wа-я\-]{2,}\.)+[\wа-я\-]+)(\/[\wа-я\-]*(\.\w{3,4})?)*(\?[\w\&\;\=\-]*)?)\]/ui', $callback, $text);
}

function wiki_href_external_replace($more){
    $link   = isset($more[9]) ? $more[9] : $more[2];
    return "<a href=\"http://{$more[1]}\" target=\"_blank\">$link</a>";
}

function wiki_link(& $text){
    $text = preg_replace_callback('/\[link\:([\w\/а-я\+]+)\s?([\w\а-я\s]*)\]/ui', 'wiki_link_replace', $text);
    $text = preg_replace_callback('/\[link\(([\w\/а-я\s]+)\,([\w\а-я\s]*)\)\]/ui', 'wiki_link_replace', $text);
}

function wiki_link_replace($more){
    $url    = Router::instance()->makeUrl($more[1]);
    $title  = $more[2] ? $more[2] : $more[1]; 
    return "<a href=\"{$url}\">$title</a>";
    
}

?>
