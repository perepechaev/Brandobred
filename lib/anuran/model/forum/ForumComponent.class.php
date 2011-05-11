<?php

require_once(PATH_MODEL . '/object/ObjectComponent.class.php');
require_once(PATH_MODEL . '/forum/ForumDataComponent.class.php');
require_once(PATH_MODEL . '/forum/ForumTemplateComponent.class.php');
require_once(PATH_MODEL . '/forum/ForumAccessorComponent.class.php');

class ForumComponent extends ObjectComponent
{
	const STATUS_UNPROVEN	= null;
	const STATUS_APPROVE	= 1;
	const STATUS_DISAPPROVE	= 2;
	const STATUS_DELETE		= 4;

    public function __construct(){
        $this->setGiver(    new ObjectGiverComponent($this) );
        $this->setData(     new ForumDataComponent($this) );
        $this->setAccessor( new ForumAccessorComponent($this) );
        $this->setList(     new ObjectListComponent() );
        $this->setTemplate( new ForumTemplateComponent() );
    }

    public function listForum($limit){
        $forum              = $this->getAccessor()->listAll($limit, 'time');
        $template           = $this->getTemplate();
        return $template->get('ForumList', array(
            'forums'    => $forum
        ));
    }

    public function listGroup(){
        return $this->getTemplate()->get('GroupList', array(
            'groups'    => $this->getAccessor()->listGroup()
        ));
    }

    public function saveMessage($id, $forumId, $text){
        $message            = $this->getData()->makeMessage();
        $message->id        = $id;
        $message->forum_id  = $forumId;
        $message->text      = $text;
        Mysql::instance()->save($message);
        dump(Mysql::instance()->last());
    }

    public function visit($ip, $get, $agent){
        $data           = $this->getData();
        $data->ip       = $ip;
        $data->time     = date('Y-m-d H:i:s');
        $data->unique   = md5($agent . $ip);
        $data->get      = $get;
        Mysql::instance()->save($data);
        return $data;
    }

    /**
     * @return ForumComponent
     */
    static public function create(){
        return new ForumComponent();
    }
}


?>