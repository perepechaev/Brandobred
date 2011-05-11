<?php

class MysqlException extends Exception
{
    const ERROR_CONNECT                 = 1;
    const ERROR_QUERY                   = 2;
    const NOT_NULL_REQUEST              = 3;
    const ERROR_FIELD_PARAM             = 4;
    const ERROR_FIELD_VALUE             = 5;
    const ERROR_METHOD_CALL             = 6;
    const EXPECT_ONE_RECORD             = 7;
    const NOT_DEFINET_VARIABLE          = 8;
    const EXPECT_MODIFY                 = 9;
    const ERROR_FIELD_LENGTH            = 10;
    const NOT_FIND_FIELD_IN_STRUCTURE   = 11;
    const CONDITION_NOT_INSTANCE        = 12;
    const DATA_NOT_INSTANCE             = 13;
    const CONDTITION_IN_EMPTY           = 14;
    const CONDTITION_IN_NOT_ARRAY       = 15;
    const TRANSACTION_NESTED_REVERT     = 16; 
    const TRANSACTION_ERROR_COUNT       = 17;
    
    const DUPLICATE_ENTITY              = 1062;
    const UNKNOWN_TABLE                 = 1051;
    const DATABASE_NOT_FOUND            = 1049;
    

    private $realException;
    
    private function setRealException(Exception $e = null){
        $this->realException    = $e;
    }
    
    public function throwRealException(){
        if ($this->realException){
            $e = $this->realException;
            $this->realException = null;
            throw $e;
        }
    }
    
    static public function transactionErrorCount(){
        throw new MysqlException("При старте транзакции счетчик отрицательный", self::TRANSACTION_ERROR_COUNT);
    }
    
    static public function transactionNestedRevert(Exception $e = null){
        $exception = new MysqlException("Во вложенной транзакции случился реверт", self::TRANSACTION_NESTED_REVERT);
        $exception->setRealException($e);
        throw $exception;
    }
    
    static public function connectionFailed(){
        throw new MysqlException(mysql_error(), self::ERROR_CONNECT);
    }

    static public function queryFailed($query){
        throw new MysqlException(mysql_error() . " '$query'", mysql_errno() );
    }

    static public function nullNotRequest($table, $name){
        throw new MysqlException("В объекте `$table`.`$name` не может быть NULL", self::NOT_NULL_REQUEST );
    }

    static public function wrongFieldParam($table, $type, $param){
        throw new MysqlException("В таблице `$table` тип '$type' содержит неверный параметр '$param'", self::ERROR_FIELD_PARAM);
    }

    static public function wrongFieldValue($table, $field, $value){
        throw new MysqlException("В объекте `$table` неверный параметр для поля `$field`, значение не может быть " . var_export($value, true), self::ERROR_FIELD_VALUE );
    }
    
    static public function wrongFieldLength($table, $field, $length){
        throw new MysqlException("Длина строки превышает размер поля `$table`.`$field`: " . $length, self::ERROR_FIELD_LENGTH);
    }

    static public function methodNotFound($obj, $method){
        throw new MysqlException('Класс '.get_class($obj) . ' не имееет метода ' . $method . '()', self::ERROR_METHOD_CALL );
    }

    static public function expectOneRecord($count){
        throw new MysqlException('Ожидается получение одной записи из БД, получено: ' . $count, self::EXPECT_ONE_RECORD);
    }
    
    static public function expectModify(){
        throw new MysqlException('Операция изменения не выполнена', self::EXPECT_MODIFY);
    }

    static public function accessToNotDefinedVariable(){
        throw new MysqlException('Переменная не определена', self::NOT_DEFINET_VARIABLE );
    }
    
    static public function notFindFieldInStructure($table_name, $field){
        throw new MysqlException("Поле `{$table_name}`.`{$field}` не найдено.", self::NOT_FIND_FIELD_IN_STRUCTURE);
    }
    
    static public function conditionNotInstance($criteria){
        throw new MysqlException("Не установлен класс MysqlCondition в объекте критерии '" . get_class($criteria) . "'", self::CONDITION_NOT_INSTANCE);
    }
    
    static public function dataNotInstance($object){
        throw new MysqlException("Не установлен класс MysqlData в объекте '" . get_class($object) . "'", self::DATA_NOT_INSTANCE);
    }
    
    static public function condtionInEmpty(){
        throw new MysqlException("Метод Condition::in() не принимает на вход пустые массивы", self::CONDTITION_IN_EMPTY);
    }
    
    static public function condtionInNotArray($values){
        throw new MysqlException("Метод Condition::in() принимает массивы, получен же: " . var_export($values, true), self::CONDTITION_IN_NOT_ARRAY);
    }
    
    static public function verificationConditionIn($values){
        if (!is_array($values)) self::condtionInNotArray($values);
        if (count($values) === 0) self::condtionInEmpty();
    }

/*
    static public function objectNotInitialization(){
        throw new MysqlException('Объект не инициализован Mysql::init()');
    }

    static public function resultMustBeArray($res){
        throw new MysqlException("Результат должен быть массивом: ".var_export($res, true));
    }

    static public function dublicateField($field){
        throw new MysqlException("Дублирование поля $field");
    }

    static public function duplicateData($name){
        throw new MysqlException("Дублирование объекта '$name' extends MysqlData");
    }

    static public function classNotInstanceOfMysqlData($class){
        throw new MysqlException("Класс $class должен быть наследован от MysqlData");
    }
*/
}

?>