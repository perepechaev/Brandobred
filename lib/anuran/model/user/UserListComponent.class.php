<?php

require_once(PATH_MODEL . '/user/UserDataComponent.class.php');
require_once(PATH_MODEL . '/user/UserIterator.class.php');
require_once(PATH_MODEL . '/object/ObjectListComponent.class.php');

class UserListComponent extends ObjectListComponent implements IteratorAggregate
{
    protected $users  = array();
    protected $count  = 0;

    /**
     * Найти пользователя зная его имя и пароль
     *
     * Если такой пользователь с паролем существует,
     * то метод возвращает объект User, иначе bool(false)
     *
     * @param string $user
     * @param string $pwd
     * @return User
     */
    public function find($user, $pwd){
        $res    = isset($this->users[$user]) && ($this->users[$user]->password === $pwd);
        $res    = ($res) ? $this->users[$user] : false;
        return $res;
    }
    
    public function count(){
        return $this->count;
    }

    /**
     * Добавить пользователя в список
     *
     * Добавляет пользователя в список пользователей
     * Метод не сохраняет пользователь, а добавленные
     * пользователи только на момент выполнения скрипта
     *
     * @param string $user
     * @param string $pwd
     */
    public function add(MysqlData $user){
        equal(!isset($this->users[ $user->login ]), "Пользователь {$user->login} уже добавлен в список.");

        $this->users[ $user->login ] = $user;
        $this->count++;
    }

    public function getIterator() {
        return new UserIterator($this->users);
    }
}

?>