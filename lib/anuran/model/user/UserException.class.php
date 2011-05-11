<?php

class UserException extends Exception
{
    const NOT_SELECTED_USER                 = 1;
    const ADD_USER                          = 2;
    const SUBSTITUTION_SESSION              = 3;
    const USER_NOT_AUTHORIZE                = 4;
    const USER_LIST_NOT_DEFINED             = 5;
    const EMPTY_NAME_OR_PWD                 = 6;
    const WRONG_USER_NAME                   = 7;
    const USER_NAME_IS_LONG                 = 8;
    const USER_PWd_IS_LONG                  = 9;
    
    const USER_NAME_IS_SHORT                = 11;
    const WRONG_GUEST_LOGIN_OR_PASSWORD     = 12;
    const WRONG_USER_FIELDS_ON_REGISTRATION = 13;
    const WRONG_USER_FIELDS_ON_EDIT         = 14;
    const WRONG_USER_FIELDS_ON_CHANGE_PASS  = 15;
    const WRONG_USER_FIELD                  = 16;
    

    static public function notSelectedUser(){
        throw new UserException('Нет информации о пользователе', self::NOT_SELECTED_USER );
    }
    
    public function notFoundUser(){
        $this->execute('Не найден пользователь', self::NOT_SELECTED_USER);
    }

    static public function addUser($user){
        throw new UserException("Пользователь '$user' был уже добавлен в список", self::ADD_USER );
    }

    static public function substitutionSession($oldIp, $newIp){
        throw new UserException("Подмена пользовательской сессии. Последний ip = ($oldIp), новый ($newIp)", self::SUBSTITUTION_SESSION );
    }

    static public function userNotAuthorize(){
        throw new UserException('Пользователь незалогинен', self::USER_NOT_AUTHORIZE );
    }

    static public function userListNotDefined(){
        throw new UserException("Не найден объект UserList", self::USER_LIST_NOT_DEFINED);
    }
    
    static public function wrongGuestLoginOrPassword(){
        throw new UserException("Неверный логин или пароль", self::WRONG_GUEST_LOGIN_OR_PASSWORD);
    }

    static public function testUserNamePwd($user, $pwd){
        if (empty($user) || empty($pwd)){
            throw new UserException("Пустое имя пользователя или пароль", self::EMPTY_NAME_OR_PWD);
        }
        
        if (mb_strlen($user) < USER_NAME_MIN_LENGTH){
            throw new UserException("Имя пользователя слишком короткое", self::USER_NAME_IS_SHORT);
        }

        if (mb_strlen($user) > USER_NAME_MAX_LENGTH){
            throw new UserException("Имя пользователя слишком длинное: $user, count(login) = " . mb_strlen($user), self::USER_NAME_IS_LONG);
        }

        if (mb_strlen($pwd) > USER_PWD_MAX_LENGTH){
            throw new UserException("Пароль пользователя слишком длинный: $pwd, count(pwd) = " . strlen($pwd), self::USER_PWd_IS_LONG);
        }

        if (!preg_match('/^[a-z_\d\.@]+$/Ui', $user)){
            throw new UserException("Некорректное имя пользователя, используются только латинские буквы, цифры и символы подчеркивания", self::WRONG_USER_NAME);
        }
    }
    
    static public function wrongFields(){
        throw new UserException('Неверно заполнены поля у пользователя', self::WRONG_USER_FIELDS);
    }
    
    protected function execute($message, $code){
        $this->code     = $code;
        $this->message  = $message;
        throw $this;
    }
    
    
};

?>