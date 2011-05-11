<?php

require_once(PATH_MODEL . '/file/File.class.php');

class Cache
{
    static private $path   = 'cache/';

    public function put($tag, $filename, $data){
//        $tag    = (is_array($tag)) ? $tag : array($tag);
        $path   = Cache::$path . $tag;
        $file   = File::create();

        File::createIsNotExistDirectory($path);
        $file->setPath($path);
        $file->setFileName($filename);
        $file->write(serialize($data));
    }

    public function take($tag, $filename){
        return unserialize(file_get_contents(Cache::$path . $tag . '/' . $filename));
    }

    public function generateFileName($tag, $method, $param = array()){
        return md5(serialize($method) . serialize($param) . $tag);
    }

    public function getFileName($tag, $method, $param = array()){
        return Cache::$path . $tag . '/' . $this->generateFileName($tag, $method, $param);
    }

    static function execute($tag, $method, $param = array(), $expression = 7200){
        $cache      = new Cache();

        $path       = Cache::$path . $tag . '/';
        $filename   = $cache->generateFileName($tag, $method, $param);
        if (!is_readable($path . $filename) || ((filemtime($path . $filename) + $expression) < time())){
            $data       = call_user_func_array($method, $param);
            $cache->put($tag, $filename, $data);
        }

        return $cache->take($tag, $filename);
    }

    static public function initialization($path){
        $path           = trim($path, '/') . '/';
        File::createIsNotExistDirectory($path, 0755);
        Cache::$path    = $path;
    }

    /**
     * @return Cache
     */
    static public function create(){
        return new Cache();
    }
}

?>