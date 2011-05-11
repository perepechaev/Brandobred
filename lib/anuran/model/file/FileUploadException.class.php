<?php

require_once(dirname(__FILE__) . '/FileException.class.php');

class FileUploadException extends FileException
{
    const NOT_INDICATE_UPLOAD_FILE      = 100;
    const CANT_WRITE                    = 101;
    const CANT_MOVE_UPLOAD_FILE         = 102;
    const MAX_FILE_SIZE                 = 104;
    const NOT_ALLOWED_TYPE              = 105;
    const EXPECT_FILE                   = 106;

    static public function NotIndicateUploadFile(){
        throw new FileUploadException('Не указано имя поля в форме', self::NOT_INDICATE_UPLOAD_FILE);
    }

    static public function cantWrite($path){
        $path   = dirname($path);
        if (!is_writable($path)) {
            throw new FileUploadException('Невозможно произвести запись в :'.$path, self::CANT_WRITE );
        }
    }

    static public function maxFileSize($fileSize, $limit){
        if ($fileSize > $limit){
            throw new FileUploadException('Файл превышает допустимый размер: '.$fileSize . ' байт. Вы можешет загружать не больше '.$limit .' байт', self::MAX_FILE_SIZE );
        }
    }

    static public function cantMoveUploadFile($file='', $fileto=''){
        $e  = new FileUploadException("Невозможно переместить загруженный файл: '$file' -> '$fileto'", self::CANT_MOVE_UPLOAD_FILE );
        FileException::errorLog('UPLOAD_MOVE_FILE', $e);
        throw $e;
    }

    static public function notAllowedType($is, $type = ''){
        if (!$is) {
            throw new FileUploadException("Файлы ($type) запрещено сохранять на сервере", self::NOT_ALLOWED_TYPE );
        }
    }

    static public function UploadError($code = null){
        switch ($code){
            case UPLOAD_ERR_OK:
                return false;
                break;
            case UPLOAD_ERR_INI_SIZE:
                throw new FileUploadException("Размер принятого файла превысил максимально допустимый размер, который задан директивой upload_max_filesize конфигурационного файла php.ini.", UPLOAD_ERR_INI_SIZE);
            case UPLOAD_ERR_FORM_SIZE:
                throw new FileUploadException("Размер загружаемого файла превысил значение MAX_FILE_SIZE, указанное в HTML-форме.", UPLOAD_ERR_FORM_SIZE);
            case UPLOAD_ERR_PARTIAL:
                throw new FileUploadException("Загружаемый файл был получен только частично.", UPLOAD_ERR_PARTIAL);
            case UPLOAD_ERR_NO_FILE:
                throw new FileUploadException("Файл не был загружен.", UPLOAD_ERR_NO_FILE);
            default:
                throw new FileUploadException("Ошибка загрузки файла на сервер", $code);
        }
    }
    
    static public function expectFile(){
        throw new FileUploadException('Ожидается получние файла', self::EXPECT_FILE);
    }
}

?>