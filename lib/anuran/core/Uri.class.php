<?php

/**
 * Класс для работы с урлами
 *
 */
class Uri
{
    /**
     * Добавить к уже существующему урлу GET-параметры
     *
     */
    static public function buildUrl($uri, $extra = array()){
        $query      = (($pos = strpos($uri, '?')) !== false) ? substr($uri, $pos+1) : "";
        parse_str($query, $params);
        $params     = array_merge($params, $extra);
        $uri        = ($pos !== false) ? substr($uri, 0, $pos) : $uri;
        $uri       .= count($params) ? rtrim('?' . http_build_query($params), '?') : '';
        return $uri;
    }
    
    static public function add($extra = array()){
        return self::buildUrl( Router::instance()->getUrl(), $extra );
    }
}

?>