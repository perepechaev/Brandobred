<?php

require_once(dirname(__FILE__) . '/FileIterator.class.php');
require_once(dirname(__FILE__) . '/FileList.class.php');

class File
{
    protected $fileName;
    protected $path;
    protected $description;
    protected $author;

    /**
     * @return File
     */
    public static function create(){
        return new File();
    }

    public function setFileName($filename){
        $this->fileName = basename($filename);
        return $this;
    }

    public function setDescription($desc){
        $this->description  = $desc;
    }

    public function setAuthor($author){
        $this->author   = $author;
    }

    public function setPath($path){
        $this->path = rtrim($path, '/') . '/';
    }

    public function getPath(){
        return $this->path;
    }

    public function getFileName(){
        return $this->fileName;
    }

    public function getDescription(){
        return $this->description;
    }

    public function getAuthor(){
        return $this->author;
    }

    public function readDescription(){
        $currentFile    = $this->getPath() . $this->getFileName();
        $currentFile    = mb_convert_encoding($currentFile, FILESYSTEM_ENCODE, 'UTF-8');


        FileException::isReadable($currentFile);
        $handle         = fopen($currentFile, 'rb');
        $description    = fgets($handle, 20) . fgets($handle, 20) . fgets($handle, 20);
        $description    = ($fromEcode = mb_detect_encoding($description, UPLOAD_DETECT_ENCODE))
                        ? mb_convert_encoding($description, 'UTF-8', $fromEcode)
                        : $description;
        $description    = htmlspecialchars($description);
        $description    = nl2br($description);
        fclose($handle);
        $this->setDescription($description);
    }

    public function getContent(){
        return $this->read();
    }

    public function read(){
        $currentFile    = $this->getPath() . $this->getFileName();
        $currentFile    = mb_convert_encoding($currentFile, FILESYSTEM_ENCODE, 'UTF-8');
        FileException::isReadable($currentFile);

        // NB: Думаю здесь подавление и обработку ошибки ставить незачем, т.к.
        // файл уже проверен на читабельность
        $content        = file_get_contents($currentFile);
        $content        = ($fromEcode = mb_detect_encoding($content, UPLOAD_DETECT_ENCODE))
                        ? mb_convert_encoding($content, 'UTF-8', $fromEcode)
                        : $content;
        return $content;
    }

    public function write($content){
        $currentFile    = $this->getPath() . $this->getFileName();
        $currentFile    = mb_convert_encoding($currentFile, FILESYSTEM_ENCODE, 'UTF-8');

        @file_put_contents($currentFile, $content);
        FileException::isWritable($currentFile);
    }

    public function drop(){
        $filename   = $this->getPath() . $this->getFileName();
        $filename   = mb_convert_encoding($filename, FILESYSTEM_ENCODE, 'UTF-8');
        FileException::isReadable($filename);
        if (!unlink($filename)) {
            FileException::cantDeleteFile($filename);
        }
    }

    static public function createIsNotExistDirectory($path, $mode = 0755){
        if (!is_dir($path)){
            try{
                mkdir($path, $mode, true);
            }
            catch (Exception $e){
                if ($e->getCode() === E_WARNING) FileException::cantCreateDirectory($path);
                throw $e;
            }
        }
    }
}

?>