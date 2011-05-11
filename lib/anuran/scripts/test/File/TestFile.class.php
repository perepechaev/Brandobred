<?php

require_once(dirname(__FILE__) . '/../TestHead.php');
require_once(PATH_MODEL . '/file/File.class.php');
//require_once(PATH_MODEL . '/user/UserList.class.php');


class TestFile extends Test
{
    public function test_openBasedir(){
//        print_r(ini_get_all());
    }

    public function test_notFoundUserDirectory(){
        $filelist   = new FileList();
        if (file_exists(PATH_UPLOAD . '/not_found_user')){
            $this->result('Temp directory is exists', 'ERROR (требуется изменить тестовую директорию)');
        }
        try{
            $filelist->prepareUser('not_found_user');
        }
        catch (FileException $e){
            if ($e->getCode() !== FileException::NOT_DIRECTORY_EXISTS ){
                throw $e;
            }
        }
        $this->result('Test empty user directory', 'ok');
    }

    public function test_FileCache(){
        $list   = UserList::instance();
        $path   = dirname(__FILE__) . '/upload2';
        $cache  = dirname(__FILE__) . '/cache';

//        $this->prepareFileCache(10, 10,    $path, $cache);
//        $this->prepareFileCache(1000, 10,  $path, $cache);
//        $this->prepareFileCache(10, 1000,  $path, $cache);
//        $this->prepareFileCache(10, 10000, $path, $cache);
//        $this->prepareFileCache(100, 100,  $path, $cache);

//        $this->result("  Count files", "17 982");
//        $this->result("  Count find files", "999");
//        $this->result("  SpeedTest. Prepare from cache ver.1", "0.19s");
//        $this->result("  SpeedTest. Prepare from cache ver.2", "23.1s");
//        $this->result("  SpeedTest. Find from list by name", "0.10s");
//        $this->result("  SpeedTest. Count List", "0.00s");

        $this->result('List loaded', 'ok');
    }

    public function test_accessPermision(){
        FileList::create()->prepareAll_2();
    }


    function prepareFileCache($iUser, $iFiles, $sDirectory, $sCache){
        $this->result('Start SpeedTest', 'ok');
        $fill   = 'asklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjfasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjfasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjfasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjasklfjkahghja;lkdsjfsadfsjdfklajsdkfa ksjdfk ljdsalkjf alksdjf lsakjf;asdjf';

        $path   = $sDirectory;
        UserList::destroy();
        for ($i = 1; $i <= $iUser; $i++){
            $userpath   = $path . "/test$i";
            File::createIsNotExistDirectory($userpath);
            for ($j = 1; $j <= $iFiles; $j++){
                file_put_contents($userpath . "/test$j", $fill);
            }
            UserList::instance()->add("test$i", "pwd$i");
        }
//        $this->result('  File is created', 'ok');

        // Подгатавливаем кэш (тут надо проследить чтобы в config.php было достаточное время для кэширования файлов)
        $list   = FileList::create();
        $list->setListPath($path);
        $list->setPathCache($sCache);

        $cache1 = microtime(1);
        $list->prepareAll();
        $cache2 = microtime(1);
//        $this->result('  PrepareAll 1 cache', $cache2 - $cache1);

        $cache1 = microtime(1);
        $list->prepareAll_2();
        $cache2 = microtime(1);
//        $this->result('  PrepareAll 2 cache', $cache2 - $cache1);
        unset($list);


        $list   = FileList::create();
        $list->setListPath($path);
        $list->setPathCache($sCache);

        $start1 = microtime(1);
        $list->prepareAll();
        $prep1  = microtime(1);
        $file1  = $list->findByName('file1');
        $find1  = microtime(1);

        $list1  = clone $list;
        $list_count1    = $list->count();
        $find_count1    = $file1->count();

        $start2 = microtime(1);
        $list->prepareAll_2();
        $prep2  = microtime(1);
        $file1  = $list->findByName('file1');
        $find2  = microtime(1);

        $list_count2    = $list->count();
        $find_count2    = $file1->count();

//        $this->result("  List1 count All", $list_count1);
//        $this->result("  Find count", $find_count1);
//        $this->result("  Prepare All files from cache 1", $prep1 - $start1);
//        $this->result("  Find All) files from cache 1", $find1 - $prep1);
//
//        $this->result("  List count All", $list_count2);
//        $this->result("  Find count", $find_count2);
//        $this->result("  Prepare All files from cache 2", $prep2 - $start2);
//        $this->result("  Find All files from cache 2", $find2 - $prep2);
//
//        $this->result("  Time for count", $start2 - $find1);

        foreach ($list1 as $file){
            $file->drop();
        }

        $this->deleteDirectory($sDirectory);
        $this->deleteDirectory($sCache);

//        $this->result("Complete SpeedTest", 'ok');
//        echo "\n";
    }

    protected function deleteDirectory($sDir){

        $directory  = $sDir;
        $result = array();
        $d = dir(rtrim($directory, '/') . '/');
        while (false !== ($entry = $d->read())) {
            if ( ($entry === '.') || ($entry === '..') ){
                continue;
            }

            if (is_dir($d->path . $entry)) {
                $this->deleteDirectory($d->path . $entry);
            }

            if (is_file($d->path . $entry)) {
                unlink($d->path . $entry);
            }
        }
        $d->close();
        rmdir($sDir);
    }

}
// XXX: Временно выключен
//$test   = new TestFile();
//$test->complete();

?>