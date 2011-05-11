<?php

require_once(PATH_MODEL . '/file/FileListCacheException.class.php');

class FileListCache
{
    private $result;
    private $fileList;

    private $expiration;
    private $method;
    private $cacheFile;

    static private $results     = array();

    public function setCacheFile($filename){
        $dir    = dirname($filename);
        if (!is_dir($dir) && !mkdir($dir, 0755, true)){
            FileException::cantCreateDirectory($dir);
        }

        if (!file_exists($filename)){
            $handle = @fopen($filename, 'w');
            fclose($handle);
        }

        FileException::isWritable($filename);

        if (!is_writable($filename)) {
            FileException::cantCreateFile($filename);
        }

        $this->cacheFile= $filename;
    }

    /**
     * Установить время жизни кэша
     *
     * @param int $time Количество секунд
     */
    public function setExpiration($time){
        $this->expiration   = $time;
    }

    public function setMethod($method){
        $this->method   = $method;
    }

    public function getResult(){
        $time   = filemtime($this->cacheFile);
        $result = array();
        if ( (filesize($this->cacheFile) === 0) || ((@time() - $time) > $this->expiration) ){
            $obj    = $this->method[0];
            $method = $this->method[1];
            $param  = isset($this->method[2]) ? $this->method[2] : array();

            $result = call_user_func_array(array($obj, $method), $param);
            file_put_contents($this->cacheFile, serialize($result));
        }
        else {
            $result = $this->getResultFromCache();
        }
        return $result;
    }

    public function getResultFromCache(){
        if (!isset(self::$results[$this->cacheFile])){
            self::$results[$this->cacheFile]    = unserialize(file_get_contents($this->cacheFile));
        }

        return self::$results[$this->cacheFile];
    }

    public function prepare(){
        if (empty($this->cacheFile)) {
            FileListCacheException::notInstanceCacheFile();
        }

        if (isset($this->fileList)){
            throw new FileListCacheException('Попытка второй раз инициализировать данные');
        }

        $files  = file_get_contents($this->cacheFile);
        $this->fileList = unserialize($files);
    }

    public function getList(){
        if (empty($this->fileList)){
            throw new FileListCacheException('Файлов в кэше не обнаружено');
        }

        return $this->fileList;
    }

    /**
     * @return FileListCache
     */
    static public function create(){
        return new FileListCache();
    }

}

?>