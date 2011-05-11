<?php

require_once(PATH_MODEL . '/file/FileListCache.class.php');

class FileList implements IteratorAggregate
{
    private $items  = array();
    private $count  = 0;

    /**
     * Переменная-критерий поиска
     *
     * @var string
     */
    private $search;

    /**
     * Путь к файлам
     *
     * PATH_UPLOAD for default
     *
     * @var string
     */
    private $path;

    /**
     * Путь к кэшу
     *
     * FILE_CACHE_PATH for default
     *
     * @var string
     */
    private $pathCache;

    /**
     * @return FileIterator
     */
    public function getIterator() {
        return new FileIterator($this->items);
    }

    public function getPagerIterator($page){
        $items  = array_chunk($this->items, FILE_COUNT_PER_PAGE, true);
        return new FileIterator($items[$page-1], count($items));
    }

    public function count(){
        return $this->count;
    }

    public function add(File $file) {
        if (array_search($file, $this->items) !== false){
            return false;
        }
        $this->items[]  = $file;
        $this->count++;
    }

    /**
     * Подготовить список всех файлов
     *
     * Используется механизм кэширования
     * Необходима так же информация обо всех
     * пользователях из класса UserList
     *
     * Имеет существенный недостаток: если список
     * файлов пользователя обновляется динамически
     * после каждой манипуляции, то общий список
     * всех файлов не всегда будет отображать
     * действительную структуру файлов. Т.е.
     * пользователь может удалить свой файл, в
     * списке файлов пользователя информация обновится
     * моментально, а в общем списке информация
     * будет обновлена в интервале
     * от одной до FILE_CACHE_TIME_ALL секунд
     *
     * Тем не менее метод представляется устойчивым
     * в связи с тем, что работает напрямую со
     * структурой файлов минуя промежуточные стадии
     *
     * Способ предпочителен при большом количестве
     * пользователей и небольшом количестве файлов
     * у каждого пользователя
     *
     * @return FileList
     */
    public function prepareAll() {
        $cache          = FileListCache::create();
        $cache->setExpiration( FILE_CACHE_TIME_ALL );
        $cache->setCacheFile( $this->getPathCache() . '/list_all.cache' );
        $cache->setMethod( array($this, 'getFiles') );

        $this->items    = $cache->getResult();

        $this->count    = count($this->items);

        return $this;
    }

    /**
     * Подготовить список всех файлов
     *
     * Дублирует функционал prepareAll
     * Существует разница лишь в реализации
     * Если первый способ проходится по всем
     * файлам раз в FILE_CACHE_TIME_ALL секунд,
     * то второй способ проходится по всем
     * закэшированным файлам пользователей
     * каждый раз при обращении.
     *
     * Имеет недостаток: метод не работает с
     * файловой системой напрямую, а всего
     * лишь с кэшем пользовательских файлов
     * Т.е. если вдруг по непонятным причинам
     * пользовательский кэш будет содержать
     * неверную информцию, то ошибка перейдет
     * так же в общий список файлов. К тому же,
     * хоть операция получения общего списка
     * файлов в этом методе является недорогой,
     * нам приходится при каждом запросе обращаться
     * к некоторому числу файлов N, где N -
     * количество пользователей в системе
     *
     * Имеет неплохой плюс: в случае правильной
     * обработке пользовательских кэш файлов мы
     * в любой момент времени видим изменения
     * в файловой системе
     *
     * Способ предпочителен при небольшом
     * количестве пользователей, но большим
     * количеством файлов у каждого пользователя
     *
     * @return FileList
     */
    public function prepareAll_2(){
        $items      = array();
        $cacheFiles = UserList::instance()->getIterator();
        $cache      = FileListCache::create();
        foreach ($cacheFiles as $user){
            $cachefile      = $this->getPathCache() . '/listuser_' .$user->getName().'.cache';
            $cache->setCacheFile($cachefile);
            if (!filesize($cachefile)){
                $result = FileList::prepareUser($user->getName())->getItems();
            }
            else {
                $result = $cache->getResultFromCache();
            }
            $items          = array_merge($items, $result);
        }

        $this->items    = $items;
        $this->count    = count($this->items);

        return $this;
    }

    /**
     * Подготовить список файлов пользователя
     *
     * @param string $username
     * @return FileList
     */
    public function prepareUser($username){
        $this->resetItems();

        $cache          = FileListCache::create();
        $cache->setExpiration( FILE_CACHE_TIME_USER );
        $cache->setCacheFile( $this->getPathCache() . "/listuser_{$username}.cache" );
        $cache->setMethod(array($this, 'getUserFiles', array(
            $username
        )));
        $this->items    = $cache->getResult();
        $this->count    = count($this->items);

        return $this;
    }

    /**
     * Обновить информацию в кэше
     *
     * Метод обновляет информацию о пользовательский файлах
     * Информация берется из уже загруженного списка файллов
     *
     * @param string $username
     */
    public function updateUser($username){
        $cache  = FileListCache::create();
        $cache->setExpiration( -100 );
        $cache->setCacheFile( $this->getPathCache() . "/listuser_{$username}.cache" );
        $cache->setMethod(array($this, 'getItems'));
        $cache->getResult();
    }

    /**
     * Найти в текущем списке файл с
     * именем $filename
     *
     * @param string $filename
     * @return FileList
     */
    public function findByName($filename){
        $search         = $this->search;

        $this->search   = $filename;
        $items          = array_filter($this->items, array($this, 'compareByName'));
        $list           = new FileList();
        $list->items    = $items;
        $list->count    = count($items);

        $this->search   = $search;
        return $list;
    }

    public function dropByName($filename){
        $search         = $this->search;

        $this->search   = $filename;
        $items          = array_filter($this->items, array($this, 'compareByName'));
        foreach ($items as $key => $file){
            $file->drop();
            unset($this->items[$key]);
        }
        unset($items);

        $this->search   = $search;
        return $this;
    }

    /**
     * Подходит ли файл по имени
     *
     * @param File $file
     * @return bool
     */
    protected function compareByName(File $file){
        return $file->getFileName() === $this->search;
    }

    /**
     * Получить список пользовательских
     * файлов зная имя пользователя
     *
     * @param string $user
     * @return array
     */
    public function getUserFiles($user){
        // NB: метод приходится держать как public т.к. вызывается из FileListCache
        $path   = $this->getListPath() . '/' . $user . '/';
        try {
            $files  = $this->readFiles($path, 0);
        }
        catch (FileException $e){
            if ($e->getCode() === FileException::NOT_DIRECTORY_EXISTS ){
                return $this->items;
            }
            throw $e;
        }

        foreach ($files as $fileName => $val) {
            if (is_array($val)) {
                continue;
            }

            try{
                $file = File::create();
                $file->setPath($path);
                $file->setAuthor($user);
                $file->setFileName($fileName);
                $file->readDescription();
                $this->add($file);
            }
            catch (Exception $e){
                dump($e->getMessage());
                // XXX: Проглатываем обработку, думаю следует вести логи
            }
        }

        return $this->items;
    }

    /**
     * Получить список всех Upload-файлов
     *
     * @param string $directory
     * @return array
     */
    public function getFiles(){
        // NB: метод приходится держать как public т.к. вызывается из FileListCache
        $dir    = $this->getListPath();
        $files  = $this->readFiles($dir, 0);
        foreach ($files as $user => $userFiles){
            $this->getUserFiles($user);
        }
        return $this->items;
    }

    public function setListPath($path){
        $this->path = $path;
    }

    public function getListPath(){
        return isset($this->path) ? $this->path : PATH_UPLOAD;
    }

    public function setPathCache($sPathCache){
        File::createIsNotExistDirectory($sPathCache);
        $this->pathCache    = $sPathCache;
    }

    public function getPathCache(){
        return isset($this->pathCache) ? $this->pathCache : FILE_CACHE_PATH;
    }

    /**
     * Возвращает список файлов в указанном каталоге
     *
     * Каталоги '.' и '..' игнорируются.
     * Возвращает массив, ключи массива - имена файлов
     * или каталогов. Значения массива - для каталогов
     * используется array(), для файлов bool(true)
     * второй параметр - глубина вложений
     *
     * NB: Возможно следует объеденить с методом $this->getFiles,
     * надо подумать о целесообразности разделения этих методов
     *
     * <code>
     *   $list = FileList::create()->readFiles('/etc');
     *   print_r($list);
     *   // Array(
     *   //   'userdir'     => array(
     *   //     'user1'         => array(
     *   //       'file1'           => true,
     *   //       'file2'           => true
     *   //     ),
     *   //   ),
     *   //   'systemdir'   => array(),
     *   //   'httpd.conf'  => true
     *   // )
     * </code>
     *
     * @param string $directory
     * @param int $dep Глубина вложений
     */
    protected function readFiles($directory, $dep = 0){

        FileException::notDirectoryExists($directory);

        $result = array();
        $d = dir(rtrim($directory, '/') . '/');
        while (false !== ($entry = $d->read())) {
            if ( ($entry === '.') || ($entry === '..') ){
                continue;
            }

            $correctName    = mb_convert_encoding($entry, 'UTF-8', FILESYSTEM_ENCODE);

            if (is_dir($d->path . $entry)) {
                $result[$correctName]   = ($dep) ? $this->readFiles($d->path . $entry) : array();
            }

            if (is_file($d->path . $entry)) {
                $result[$correctName] = true;
            }
        }
        $d->close();
        return $result;
    }

    private function resetItems(){
        $this->items    = array();
        $this->count    = 0;
    }

    public function getItems(){
        return $this->items;
    }

    /**
     * @return FileList
     */
    static public function create(){
        return new FileList();
    }
}

?>
