<?php

class SiteSkeletonException extends Exception
{
    const EXPECTED_ARRAY    = 1;
    const EMPTY_STRUCTURE   = 2;
    const URL_INVALID       = 3;
    const MUCH_ACTIONS      = 4;
    const FILE_NOT_FOUND    = 5;
    const CLASS_NOT_FOUND   = 6;
    const WRAPPER_NOT_FOUND = 7;
    const PAGE_INCORRECT    = 8;
    const WRONG_PARAMETERS  = 9;
    const COOKIE_DOES_NOT_INSTANCE = 10;

    static public function expectedArray($param = null){
        throw new SiteSkeletonException('Ожидается массив вместо: '.var_export($param, true), self::EXPECTED_ARRAY );
    }

    static public function emptyStructure(){
        throw new SiteSkeletonException('Структура сайта не определена, проверьте файл config.php', self::EMPTY_STRUCTURE );
    }

    static public function urlInvalid(){
        throw new SiteSkeletonException('URL is not valid, check your config.php and fix URL_SITE constant', self::URL_INVALID );
    }

    static public function muchActions($url = ''){
        throw new SiteSkeletonException('Определено несколько действий для URL: '.$url . '. Проверьте в файле настроек config.php $URL_REWRITE', self::MUCH_ACTIONS );
    }

    static public function fileNotFound($pageName){
        throw new SiteSkeletonException('Файл не найден или недоступен для чтения: '.PATH_PAGE .'/'.$pageName, self::FILE_NOT_FOUND );
    }

    static public function classNotFound($cName, $pageName){
        throw new SiteSkeletonException('Не найден класс '.$cName.' в файле '.PATH_PAGE.'/'.$pageName, self::CLASS_NOT_FOUND );
    }

    static public function wrapperNotFound($path){
        throw new SiteSkeletonException('Файл-обертка не найден: '.$path, self::WRAPPER_NOT_FOUND);
    }

    static public function pageIncorrect($obj){
        throw new SiteSkeletonException('Объект не наследует Page: '.get_class($obj), self::PAGE_INCORRECT);
    }

    static public function wrongParam($parramName){
        throw new SiteSkeletonException("Не найден параметр " . $parramName, self::WRONG_PARAMETERS);
    }
    
    static public function cookieDoesNotInstance(){
        throw new SiteSkeletonException("Попытка получения объекта Cookie без инстанирования", self::COOKIE_DOES_NOT_INSTANCE);
    }
}


?>