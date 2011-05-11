<?php

require_once(dirname(__FILE__) . '/../TestHead.php');

// require_once(PATH_MODEL . '/user/User.class.php');

class TestUser extends Test
{
    protected $detail   = true;

    public function test_UserList(){
        $list   = UserList::instance();
        foreach ($list as $user){
            $this->result('Test user ' .$user->getName(), 'ok');
        }
    }

    public function test_UserListAdd(){
        $list   = UserList::instance();
        $count  = $list->count();
        $list->add('topas1', 'pwd1');
        $list->add('topas2', 'pwd1');
        try {
            $list->add('topas2', 'pwd1');
        }
        catch (UserException $e){
            $result = ($e->getCode() !== UserException::ADD_USER ) ? 'ok' : 'ERROR';
            $this->result('Test duplicate user', 'ok');
        }
        $result = ($count + 2 === $list->count()) ? 'ok' : 'ERROR';
        $this->result('Test count list', $result);
    }
}

// XXX: Не актуально
//$test   = new TestUser();
//$test->complete();

?>