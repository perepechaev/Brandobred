<?php

class RouterException extends Exception
{
    const NOT_SET_PATH_TO_CLIENT     = 1;
    const NOT_FIND_CLIENT_FILE       = 2;
    const NOT_SELECT_CLIENT          = 3;
    const CLIENT_ALREDY_SELECTED     = 4;
    const NOT_SET_URL                = 5;
    const NOT_FOUND_METHOD           = 6;
    const NOT_EXIST_METHOD           = 7;

    static public function notSetPathToClient() {
        throw new RouterException("Не выбран каталог в котором расположены клинеты маршрутизатора с помощью функции setPathClient()", self::NOT_SET_PATH_TO_CLIENT );
    }
    
    static public function notFindClientFile($filename) {
        throw new RouterException("Не найден файл клиента: " . $filename, self::NOT_FIND_CLIENT_FILE);
    }
    
    static public function notSelectClient(){
        throw new RouterException("Требуется передать в метод Router::instance() имя клиент-класса", self::NOT_SELECT_CLIENT);
    }
    
    static public function clientAlredySelected(){
        throw new RouterException("Клиент маршрутизатора уже был инстанирован, переопределение недоступно.", self::CLIENT_ALREDY_SELECTED);
    }
    
    static public function notSetUrl(){
        throw new RouterException("Класс Router требует установить URL методом Router::setUrl()", self::NOT_SET_URL);
    }
    
    static public function notFoundMethod($url, $router_client_name, $name = ""){
        throw new RouterException("Не могу обработать текущий url('$url') по правилам из '$router_client_name': $name", self::NOT_FOUND_METHOD);
    }
    
    static public function existControllerMethod($controller, $method){
        if ( (!defined('TESTING_RUN') || TESTING_RUN === false) && !method_exists($controller, $method)){
            throw new RouterException("В контроллере '" . get_class($controller) . "' не найден метод '{$method}'", self::NOT_EXIST_METHOD);
        }
    }
}


?>