<?php

require_once(dirname(__FILE__) . '/../TestHead.php');
require_once(PATH_MODEL . '/file/FileUpload.class.php');


class TestFileUpload extends Test
{
    protected $detail = true;
    
    public function test_emptyAllowed(){
        // Не указан тип файлов разрешенных для загрузки
        $message    = 'Test empty allowed type';
        $file       = $this->getFileUpload();
        $result     = 'ERROR';
        try {
            $file->upload(dirname(__FILE__));
        }
        catch (FileUploadException $e){
            if ($e->getCode() === FileUploadException::NOT_ALLOWED_TYPE ){
                $result = 'ok';
            }
            else {
                throw $e;
            }
        }
        $this->result($message, $result);
    }

    public function test_allowedAllType(){
        // Разрешаем загрузки для всех файлов
        $file       = $this->getFileUpload();
        $file->addAllowedType('all');
        $result = 'ERROR';
        try {
            $file->upload(dirname(__FILE__));
        }
        catch (FileUploadException $e){
            if ($e->getCode() !== FileUploadException::CANT_MOVE_UPLOAD_FILE ){
                $this->error($e, __LINE__);
            }
            else{
                $result = 'ok';
            }
        }
        $this->result('Test allowed all type', $result);
    }

    public function test_allowedAndDisallowedType(){
        // Разрешаем для загрузки, так же указываем какой-ть левый
        $message    = 'Test allowed all type 2';
        $file       = $this->getFileUpload();
        $file->addAllowedType('all');
        $file->addDisallowedType(array('image/jpeg', 'application/exe'));
        try {
            $file->upload(dirname(__FILE__));
        }
        catch (Exception $e){
            $result = (get_class($e) === 'FileUploadException') &&
                      ($e->getCode() === FileUploadException::CANT_MOVE_UPLOAD_FILE )
                    ? 'ok' : 'ERROR' . " (". get_class($e) .", {$e->getCode()}) in " . __FILE__ . "(". __LINE__ .")";
        }
        $this->result($message, $result);
    }

    public function test_disallowedType(){

        // Запрещаемый тип файла для загрузки
        $message    = 'Test disallow type';
        $file       = $this->getFileUpload();
        $file->addAllowedType('all');
        $file->addDisallowedType(array('text/plain', 'application/exe'));
        try {
            $file->upload(dirname(__FILE__));
        }
        catch (Exception $e){
            $result = (get_class($e) === 'FileUploadException') &&
                      ($e->getCode() === FileUploadException::NOT_ALLOWED_TYPE )
                    ? 'ok' : 'ERROR' . " (". get_class($e) .", {$e->getCode()}) in " . __FILE__ . "(". __LINE__ .")";
        }
        $this->result($message, $result);
    }

    public function test_fileAllowedType(){
        $message    = 'Test file allowed type';
        $file       = $this->getFileUpload();
        $file->addAllowedType('text/plain');
        $file->addAllowedType('image/jpeg');
        $file->addAllowedType('text/plain');
        try {
            $file->upload(dirname(__FILE__));
            $result = "ERROR";
        }
        catch (FileUploadException $e){
            if ($e->getCode() !== FileUploadException::CANT_MOVE_UPLOAD_FILE ){
                throw $e;
            }
            $result = 'ok';
        }
        $this->result($message, $result);
    }

    public function test_isFileTransfered(){
        $this->createTestsFiles();
        $filename       = dirname(__FILE__) . '/test.file';

        $field          = 'test_field_name';
        $data           = array();
        $data[$field]   = $this->getFileArray(array('tmp_name'  => basename($filename)));
        $data['bad']    = $this->getFileArray(array('tmp_name'  => 'unknow_name'));

        $upload         = new FileUpload(dirname(__FILE__) . '/upload');
        equal($upload->isTransfered() === false, 'Программа сама себе нашла какой-то переданный файл через POST, я этого не делал');

        $upload->setFile($data, $field);
        equal($upload->isTransfered() === true, 'Не обнаружил передачу файла');

        $upload->setFile($data, 'unknow_field_name');
        equal($upload->isTransfered() === false, 'А вот этого мы не передавали');

        $upload->setFile($data, 'bad');
        equal($upload->isTransfered() === true, 'Файл передали, но он вроде не дошел');
        try {
            $upload->upload(dirname(__FILE__). '/upload/');
        }
        catch (FileUploadException $e){
            if ($e->getCode() !== FileUploadException::CANT_MOVE_UPLOAD_FILE) throw $e;
        }
        equal($upload->isTransfered() === true, 'Несмотря на ошибку при перемещении файла (его просто нет), мы все равно твердим, что передача файла имела место');
    }

    public function test_storeUploadFile(){
        File::createIsNotExistDirectory($dirtest    = dirname(__FILE__) . '/test/'    , 0777);
        File::createIsNotExistDirectory($dirupload  = dirname(__FILE__) . '/upload/'  , 0777);

        $fill   = "This's test file, dont't delete pls\n\rЭто тестовый файл, пжл, не удаляйте";
        $file   = file_put_contents($filename = $dirtest . 'test.file', $fill);

        $file   = File::create();
        $file->setPath($dirtest);
        $file->setFileName($dirtest . 'test.file');

        equal($filename === $file->getPath() . $file->getFileName(), "File '$filename' is not '{$file->getPath()}{$file->getFileName()}'");

        $file->read();
        equal( $file->getContent() === $fill, "Запиcали одно, получили другое... \n\n".var_export($fill, true)."\n\n".var_export($file->getContent(),true));

        $upload = new FileUpload();
        $data   = array(
            'test'  => $this->getFileArray(array('tmp_name'  => basename($filename)))
        );
        $upload->setFile($data, 'test');
        equal($upload->isTransfered());


        try {
            $upload->upload($dirupload);
        } catch (FileUploadException $e){
            if ($e->getCode() !== FileUploadException::CANT_MOVE_UPLOAD_FILE ) throw $e;
        }
    }

    public function test_maxSize(){
        $message        = 'Test max size';
        $extra['size']  = MAX_UPLOAD_FILE_SIZE + 1;
        $file           = $this->getFileUpload($extra);
        $file->addAllowedType('all');
        $result         = 'ERROR';
        try {
            $file->upload(dirname(__FILE__));
        }
        catch (FileUploadException $e){
            if ($e->getCode() === FileUploadException::MAX_FILE_SIZE ){
                $result = 'ok';
            }
        }
        $this->result($message, $result);
    }

    public function test_uploadDirectory(){
        equal( is_dir(PATH_UPLOAD), 'Directore not exist: ' . PATH_UPLOAD );
        $handle = @fopen(PATH_UPLOAD . '/test.test', 'w');
        equal(is_resource($handle), 'Not access to write: ' . PATH_UPLOAD . '/');
        fclose($handle);
        unlink(PATH_UPLOAD . '/test.test');
        $this->result('Test upload directory', 'ok');
    }

    /**
     * @return FileUpload
     */
    private function getFileUpload($extra = array(), $name = 'test'){
        $file       = new FileUpload();
        $extra      = array_merge($this->getFileArray($extra), $extra);
        $file->setFile( array($name => $extra), $name);
        $file->removeAllowedType();
        $file->removeDisallowedType();
        return $file;
    }

    private function getFileArray($extra = array()){
        return array(
            'error'     => UPLOAD_ERR_OK,
            'size'      => 1024,
            'type'      => 'text/plain',
            'name'      => 'test.file',
            'tmp_name'  => 'test.tmp'
        );
    }

    private function createTestsFiles(){
        File::createIsNotExistDirectory($dirtest    = dirname(__FILE__) . '/test/'    , 0777);
        File::createIsNotExistDirectory($dirupload  = dirname(__FILE__) . '/upload/'  , 0777);

        $fill   = "This's test file, dont't delete pls\n\rЭто тестовый файл, пжл, не удаляйте";
        $file   = file_put_contents($filename = $dirtest . 'test.file', $fill);
    }
    
    public function __destruct(){
        unlink(dirname(__FILE__) . '/test/test.file');
        rmdir(dirname(__FILE__) . '/test/');
        
        rmdir(dirname(__FILE__) . '/upload/');
    }
}

//unset($UPLOAD_ALLOWED_TYPE);
//unset($UPLOAD_DISALOWED_TYPE);

$test   = new TestFileUpload();
$test->complete();

?>