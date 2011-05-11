<?php

require_once(dirname(__FILE__) . '/FileException.class.php');

class FileListCacheException extends FileException
{
    const NOT_INSTANCE_CACHE_FILE       = 200;

    static public function notInstanceCacheFile(){
        throw new FileListCacheException('Не установлен файл кэша', self::NOT_INSTANCE_CACHE_FILE );
    }
}

?>