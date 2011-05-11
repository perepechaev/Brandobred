<?php

class TemplateException extends Exception
{
    const TEMPLATE_METHOD_NOT_FOUND     = 1;

    static public function templateMethodNotFound($obj, $methodName) {
        if (!method_exists($obj, $methodName)){
            throw new TemplateException("Не найден метод " . get_class($obj) . "::$methodName", self::TEMPLATE_METHOD_NOT_FOUND );
        }
    }
}


?>