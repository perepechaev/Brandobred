<?php

require_once(PATH_CONTROLLER . '/UserController.class.php');

class ForumAccessorComponent extends ObjectAccessorComponent
{
    public function listGroup(){
        $group          = $this->component->getData()->makeGroup();
        $group->status  = ForumComponent::STATUS_APPROVE;
        $mysql          = Mysql::instance();
        $mysql->select('WHERE `status`=:status:', $group);
        $mysql->fetch($group, $list);
        return $list;
    }

    public function listGroupForum(){
        $group          = $this->component->getData()->makeGroup();
        $groupTbl       = Mysql::instance()->getTableName($group);

        $userTbl        = Mysql::instance()->getTableName(UserController::create()->getData());

        $forum          = $this->component->getData();
        $forum->status  = ForumComponent::STATUS_APPROVE;

        $mysql          = Mysql::instance();
        $mysql->select("as f LEFT JOIN `$groupTbl` as g ON (f.`group_id` = g.`id`)
            LEFT JOIN `$userTbl` as u ON (f.`user_id` = u.`id`)
            WHERE g.`status`=:status: AND f.`status`=:status:
            ORDER BY g.id, f.id
            ", $forum,
            'f.*, g.title group_title, u.nick user_nick, u.name user_name'
        );
        $mysql->fetch($forum, $list);

        $groups         = $group->createList();
        $groupsArr      = array();
        foreach ($list as $forum){
            if (!isset($groupsArr[$forum->group_id])){
                $groupsArr[$forum->group_id]    = array();
                $group          = clone $group;
                $group->id      = $forum->group_id;
                $group->title   = $forum->group_title;
                $groups->add($group);
            }
            $groupsArr[$forum->group_id][]    = $forum;
        }

        foreach ($groups as $group){
            $list   = $forum->createList();
            foreach ($groupsArr[$group->id] as $forum){
                $list->add($forum);
            }
            $group->setForums($list);
        }

        return $groups;
    }

    public function listByGroupId($id){
        $forum          = $this->component->getData();
        $forum->status  = ForumComponent::STATUS_APPROVE;
        $forum->group_id= $id;
        $userTbl        = Mysql::instance()->getTableName(UserController::create()->getData());

        $mysql          = Mysql::instance();
        $mysql->select('as f LEFT JOIN `' . $userTbl .'` as u ON (u.id = f.user_id)
                        WHERE `group_id`=:group_id: AND f.`status`=:status:', $forum,
                        'f.*, u.nick as user_nick, u.name as user_name');
        $mysql->fetch($forum, $list);
        return $list;
    }

    public function getGroupById($id){
        $group          = $this->component->getData()->makeGroup();
        $group->id      = $id;
        Mysql::instance()->get($group, '', 'WHERE `id`=:id:');
        return $group;
    }

    public function listMessagesByForumId($id){
        $message            = $this->component->getData()->makeMessage();
        $message->status    = ForumComponent::STATUS_APPROVE;
        $message->forum_id  = $id;

        $userTbl        = Mysql::instance()->getTableName(UserController::create()->getData());

        $mysql              = Mysql::instance();
        $mysql->select("as m
            LEFT JOIN `$userTbl` u ON (u.id = m.`user_id`)
            WHERE `forum_id`=:forum_id: AND m.`status`=:status:",
            $message,
            'm.*, u.name user_name, u.nick user_nick');
        $mysql->fetch($message, $list);

        return $list;
    }

    public function getFirstMessageByForumId($forumId){
        $message            = $this->component->getData()->makeMessage();
        $message->status    = ForumComponent::STATUS_APPROVE;
        $message->forum_id  = $forumId;

        $mysql              = Mysql::instance();
        $mysql->get($message, '', 'WHERE `forum_id`=:forum_id: AND `status`=:status: AND `parent_id` IS NULL');

        return $message;
    }
}