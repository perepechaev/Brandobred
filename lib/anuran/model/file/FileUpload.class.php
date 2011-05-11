<?php

require_once(dirname(__FILE__) . '/File.class.php');
require_once(dirname(__FILE__) . '/FileUploadException.class.php');

/**
 * Файл загрузки файлов на сервер
 *
 * Используемые глобальные переменные: $UPLOAD_ALLOWED_TYPES,
 * $UPLOAD_DISALOWED_TYPE
 *
 */
class FileUpload
{
    /**
     * Имя поля в форме загрузки
     *
     * @var string
     */
    private $formName;

    /**
     * Имя загруженного файла
     *
     * NB: Удалить
     * @var string
     */
    private $fileName;

    /**
     * Массив загруженного файла,
     * Повторяет $_FILES['fieldname']
     *
     * @var array
     */
    private $file;
    
    /**
     * Путь к каталогу для сохранения загруженных файлов
     * 
     * @var string
     */
    private $path;

    /**
     * Список разрешенных для загрузки типов
     * файлов
     *
     * @var array
     */
    private $allowedType    = array();

    /**
     * Список запрещенных к загрузке типов файлов
     *
     * @var array
     */
    private $disallowedType = array();

    /**
     * Загруженный файл
     *
     * @var File
     */
    private $uploadFile     = null;

    public function __construct($path = ''){
        
        $this->path    = empty($path) ? PATH_UPLOAD : $path;
        
        if (!function_exists('fileupload_mime_allowed')){
            require_once PATH_PAGE_ETC . '/mime.php';
        }
        
        $this->addAllowedType( fileupload_mime_allowed() );
        $this->addDisallowedType( fileupload_mime_disalowed() );
    }

    public function isTransfered(){
        if (empty($this->file)){
//            FileUploadException::NotIndicateUploadFile();
        }
        return isset($this->file) && ($this->file['error'] === 0);
    }

    public function upload($path, $filename = null){
        FileUploadException::UploadError($this->file['error']);
        FileUploadException::maxFileSize($this->file['size'], MAX_UPLOAD_FILE_SIZE);
        FileUploadException::notAllowedType( $this->isAllowedType(), $this->file['type'] );
        File::createIsNotExistDirectory($path);


        $filename       = isset($filename) ? $filename : basename($this->file['name']);
        $this->fileName = rtrim($path, '/') . '/' . $filename;
        FileUploadException::cantWrite($this->fileName);

        $fileName   = mb_convert_encoding($this->fileName, FILESYSTEM_ENCODE, 'UTF-8');

        // Используем безопасное перемещение при работе сервера
        if (!@move_uploaded_file($this->file['tmp_name'], $fileName)){
            FileUploadException::cantMoveUploadFile($this->file['tmp_name'], $fileName);
        }

        /*  XXX:
            Убираю этот блок, ни к чему он нам не нужен
            Желания тестировать работу move_uploaded_file() никакого нету
            И без этого Exception-ов хватает

        // Для тестов оставляем вот такую ужасную весчь
        if (defined('TESTING_RUN') && (TESTING_RUN === true)) {
            if (!copy($this->file['tmp_name'], $fileName)) throw new FileException('Ничего не скажу');
        }

        */

        $this->uploadFile   = new File();
        $this->uploadFile->setFileName( basename($this->fileName) );
        $this->uploadFile->setPath( dirname($this->fileName));
    }


    /**
     * Добавить информацию об загружаемой файле
     *
     * Т.к. данные приходят в виде массива $_FILES, то
     * очень удобно указать первым параметр $_FILES,
     * а вторым имя поля формы с загружаемым файлом
     *
     * Проверку на то, есть ли данные опускаем, но в
     * будущем учтем, что после setFiles надо проверять
     * загружен или нет файл
     *
     * @param array $files
     * @param string $name
     */
    public function setFile($files, $name){
        $this->file = isset($files[$name]) ? $files[$name] : null;
    }

    public function setFormName($formName){
        assert(false);
        $this->formName = $formName;
    }

    public function getFileName(){
        return $this->fileName;
    }

    /**
     * @return File
     */
    public function getFile(){
        return $this->uploadFile;
    }

    /**
     * Разрешить для загрузки следующие типы файлов
     *
     * Принимается массив разрешенных типов, если же это
     * не массив, то приводится к виду array($allowed)
     * Метод так же принимает значение 'all'
     *
     * @param mixed $allowed
     */
    public function addAllowedType($allowed = array()){
        if (!is_array($allowed)){
            $allowed    = array($allowed);
        }
        $this->allowedType      = array_merge($allowed, $this->allowedType);
        $this->allowedType      = array_unique($this->allowedType);
    }

    public function removeAllowedType(){
        $this->allowedType      = array();
    }

    public function removeDisallowedType(){
        $this->disallowedType   = array();
    }

    /**
     * Запретить для загрузки следующие типы файлов
     *
     * Принимается массив разрешенных типов, если же это
     * не массив, то приводится к виду array($disallowed)
     * Если тип не указан в addAllowedType, то он считается
     * запрещеным по умолчанию. Метод addDisallowedType
     * необходим только в том случае, если среди разрешенных
     * типов присутствует тип 'all'
     *
     * @param mixed $allowed
     */
    public function addDisallowedType($disallowed = array()){
        if (!is_array($disallowed)){
            $allowed    = array($disallowed);
        }
        $this->disallowedType   = array_merge($disallowed, $this->disallowedType);
        $this->disallowedType   = array_unique($this->disallowedType);
    }

    /**
     * Проверить, разрешена ли загрузка файла по типу
     * загруженного файла
     *
     * @return bool
     */
    public function isAllowedType(){
        // По умолчанию запрещаем все типы, если не указан тип 'all'
        $typeOk = array_search('all', $this->allowedType) !== false ? true : false;

        // Теперь разрешаем все типы, указанные в настройках
        $typeOk = array_search($this->file['type'], $this->allowedType) !== false ? true : $typeOk;

        // Запрещаем полученный тип
        $typeOk = array_search($this->file['type'], $this->disallowedType) !== false ? false : $typeOk;

        return $typeOk;
    }
}