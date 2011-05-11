<?php

class ObjectComponentException extends Exception
{
    const OBJECT_NOT_INSTANCE   = 1;    // Ожидается инстанированный объект
    const OBJECT_INSTANCE       = 2;    // Ожидается чистый объект
    const PREVENT_CHANGE        = 3;    // Предотвращение изменений
    
    public function objectInstance(ObjectDataComponent $data){
        if ($data->isClean() || !$data->id){
            $this->execute('Требуется инстанированный объект ' . get_class($data), self::OBJECT_NOT_INSTANCE);
        }
    }
    
    public function objectNotInstance(ObjectDataComponent $data){
        if (!$data->isClean() && $data->id){
            $this->execute('Требуется чистый объект ' . get_class($data), self::OBJECT_INSTANCE);
        }
    }
    
    public function preventChange(ObjectDataComponent $data){
        $this->execute('Предотвращена попытка изменить поля объекта ' . get_class($data), self::PREVENT_CHANGE);
    }
    
    protected function execute($message, $code){
        $this->code     = $code;
        $this->message  = $message;
        throw $this;
    }
}

?>