<?php

class FileException extends Exception
{
    const CANT_CREATE_DIRECTORY     = 300;
    const CANT_CREATE_FILE          = 301;
    const NOT_DIRECTORY_EXISTS      = 302;
    const CANT_READ_FILE            = 303;
    const CANT_WRITE_FILE           = 304;
    const CANT_DELETE_FILE          = 305;

    static public function cantCreateDirectory($dir){
        throw new FileException("Не могу создать директорию: $dir", self::CANT_CREATE_DIRECTORY );
    }

    static public function cantCreateFile($filename){
        throw new FileException("Не могу создать файл: $filename", self::CANT_CREATE_FILE );
    }

    static public function notDirectoryExists($dir){
        if (!is_dir($dir)){
            throw new FileException("Каталог не найден: $dir", self::NOT_DIRECTORY_EXISTS );
        }
    }

    static public function isReadable($filename){
        if (!is_readable($filename)){
            throw new FileException("Не могу прочитать файл: '$filename'", self::CANT_READ_FILE );
        }
    }

    static public function isWritable($filename){
        if (!is_writable($filename)){
            throw new FileException("Ожидается что файл доступен для записи: ".$filename, self::CANT_WRITE_FILE );
        }
    }

    static public function cantDeleteFile($filename){
        throw new FileException("Ну уж совсем фантастика, такого не быает, файл не может быть удален, хотя я уверен что он доступен для записи", self::CANT_DELETE_FILE );
    }

    static public function errorLogException($action, FileException $e){
        self::errorLog("$action\tFileException({$e->getCode()})\t{$e->getMessage()}");
    }

    static public function errorLogShowFile($action, $filename, $username){
        self::errorLog("$action\tFile '$filename' not found in FileList::getUserFiles($username)");
    }

    static public function errorLog($log){
        File::createIsNotExistDirectory(dirname(FILE_ERROR_LOG_FILE));
        $remote     = SiteSkeleton::instance()->getServerValue('REMOTE_ADDR');
        $author     = (UserAuthorize::instance()->isLogged()) ? UserAuthorize::instance()->getName() : "__UNKNOW_USER__";
        $uri        = SiteSkeleton::instance()->getPage()->getUri();
        file_put_contents(FILE_ERROR_LOG_FILE, @date('Y-m-d H:i:s') . "\t$remote\t$author\t$log\tURI:'$uri'\n", FILE_APPEND );
    }
}

?>