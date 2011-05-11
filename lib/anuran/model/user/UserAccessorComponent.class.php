<?php

class UserAccessorComponent extends ObjectAccessorComponent
{

    /**
     * @return UserDataCompnent
     */
    public function getByLogin($login){
        $user           = $this->component->getData();
        $mysql          = Mysql::instance();

        try {
            $user->login    = $login;
            $mysql->get($user, '', 'WHERE `login`=:login:');
            return $user;
        } catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::EXPECT_ONE_RECORD){
                throw $e;
            }
            return false;
        }
    }
    
    
    /**
     * @return UserListComponent
     */
    public function listByLogin($login){
        $list   = $this->getData()->createList();
        $list->add( $this->getByLogin($login) );
        return $list;
    }

    /**
     * @return UserDataCompnent
     */
    public function getByName($name){
        $user           = $this->component->getData();
        $mysql          = Mysql::instance();

        try {
            $user->name     = $name;
            $mysql->get($user, '', 'WHERE `name`=:name:');
            return $user;
        } catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::EXPECT_ONE_RECORD){
                throw $e;
            }
            return false;
        }
    }
    
    /**
     * @return UserDataCompnent
     */
    public function getByMail($mail){
        $user           = $this->component->getData();
        $mysql          = Mysql::instance();

        try {
            $user->mail     = $mail;
            $mysql->get($user, '', 'WHERE `mail`=:mail:');
            return $user;
        } catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::EXPECT_ONE_RECORD){
                throw $e;
            }
            return false;
        }
    }
    
    /**
     * Получить всех пользователей из файла.
     * Все пользователи получаю права администратора
     *
     * @param   string $filename
     * @return  UserListComponent
     */
    public function listFromFile($filename){
        if (!file_exists($filename)) {
            throw new UserException('File not found: '.$filename);
        }

        static $lists   = array();
        if (isset($lists[$filename])) return $lists[$filename];

        $user           = $this->component->getData();
        $list           = $user->createList();
        $handle         = fopen($filename, 'r');
        while (($data = fgetcsv($handle, 100, "\t")) !== false) {
            $user           = clone $user;
            $user->nick     = $data[0];
            $user->password = $data[1];
            $user->role     = 1;
            $list->add($user);
        }
        fclose($handle);
        $lists[$filename] = $list;
        return $list;
    }
    
    public function listAll($limit = 5, $order = 'id'){
        return parent::listAll($limit, $order);
    }
}