<?php


/**
 * Класс отвечающий за работу файлов, которые требуется отдать пользователю
 * через http-соединение
 *
 */
class StaticFile
{

    public function execute($filename, $param = array()){
        if (!file_exists($filename)){
            header("HTTP/1.0 404 Not Found");
            return false;
        }
        $extension  = substr($filename, strrpos($filename, '.') + 1);
        $date       = date("D, d M Y H:i:s", filemtime($filename));

        $mimeTable  = array(
            'jpg'   => 'image/png',
            'jpeg'  => 'image/png',
            'gif'   => 'image/png',
            'png'   => 'image/png',
            'ico'   => 'image/x-icon',
            'css'   => 'text/css',
        );
        $mime       = $mimeTable[$extension];

        header("Content-type: $mime");
        header("Last-modified: Fri, 16 May 2008 04:59:26 GMT");

        if ($param){
            $this->giveThumbedImage($filename, $param);
        }
        else {
            $this->giveFile($filename);
        }
    }

    static private function giveCachename($filename, &$param){
        if (isset($param['resize']) && ($param['resize'] === 'large')){
            $param  = array(
                'width'     => 400,
                'height'    => '*',
                'type'      => IMAGETYPE_JPEG,
            );
        }
        else {
            $param = array(
                'width'     => 150,
                'height'    => 150,
                'type'      => IMAGETYPE_JPEG,
            );
        }

        $cachename  = Cache::create()->getFileName('image',
            array('Thumbnail', 'output'),
            array($filename,  $param)
        );
        return $cachename;
    }

    private function giveThumbedImage($filename, $param, $expression = 86400){
        $cachename  = self::giveCachename($filename, $param);

        if (!file_exists($cachename)){
            File::createIsNotExistDirectory(dirname($cachename));
            Thumbnail::output($filename, $cachename, $param);
        }
        else {
            echo file_get_contents($cachename);
        }
    }

    static public function clearCache($filename, $param){
        $cachename  = self::giveCachename($filename, $param);
        if (file_exists($cachename)){
            unlink($cachename);
        }
    }

    private function giveFile($filename){
        echo file_get_contents($filename);
    }

    static public function create(){
        return new StaticFile();
    }
}

?>