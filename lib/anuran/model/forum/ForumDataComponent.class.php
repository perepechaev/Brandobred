<?php

class ForumDataComponent extends ObjectDataComponent
{
    private $group      = null;
    private $messages   = null;

    protected function make(){
        $this->field('id',          'int',      array('request', 'unsigned', 'auto'));
        $this->field('group_id',    'int',      array('request', 'unsigned'));
        $this->field('user_id',     'int',      array('request', 'unsigned'));
        $this->field('title',       'string',   array('request'));
        $this->field('time',        'datetime', array('request'));
        $this->field('status',      'int',      array());

        $this->name('forum');
        equal(isset($this->component), 'Не выбрана компонента');
    }

    /**
     * @return  ForumGroupDataComponent
     */
    public function makeGroup(){
        return new ForumGroupDataComponent($this->component);
    }

    /**
     * @return  ForumMessageDataComponent
     */
    public function makeMessage(){
        return new ForumMessageDataComponent($this->component);
    }

    public function setGroup(ForumGroupDataComponent $group){
        $this->group        = $group;
    }

    public function setMessages(ObjectListComponent $messages){
        foreach ($messages as $m){
            $m->setForum($this);
        }
        $this->messages     = $messages;
    }

    /**
     * @return  ForumGroupDataComponent
     */
    public function getGroup(){
        assert(is_null($this->group));
        return $this->group;
    }

    /**
     * @return  MysqlList
     */
    public function getMessages(){
        assert(isset($this->messages));
        return $this->messages;
    }

    /**
     * @return ForumMessageDataComponent
     */
    public function getFirstMessage(){
        return $this->component->getAccessor()->getFirstMessageByForumId($this->id);
    }

    public function getHtmlFirstMessage(){
        try {
            $html   = "";
            $html   = htmlspecialchars($this->getFirstMessage()->text);
        }
        catch (MysqlException $e){
            if ($e->getCode() !== MysqlException::EXPECT_ONE_RECORD) throw $e;
        }
        return $html;
    }

    public function getGroupUrl(){
        $group      = $this->makeGroup();
        $group->id  = $this->group_id;

        return $group->defaultUrl;
    }

//    public function getHtmlFirstMessageBr(){
//        return nl2br($this->getHtmlFirstMessage());
//    }
}

class ForumGroupDataComponent extends ObjectDataComponent
{
    private $forums         = null;

    protected function make(){
        $this->field('id',          'int',      array('request', 'unsigned', 'auto'));
        $this->field('parent_id',   'int',      array('unsigned'));
        $this->field('object_id',   'int',      array('unsigned'));
        $this->field('title',       'string',   array('request'));
        $this->field('table',       'string',   array('length'=>32));
        $this->field('status',      'int',      array());

        $this->name('forum_group');

        $this->setGiver($this->component->getGiver());
    }

    public function setForums(ObjectListComponent $forums){
        $this->forums       = $forums;
    }

    /**
     * @return ObjectListComponent
     */
    public function getForums(){
        assert( isset($this->forums) );
        return $this->forums;
    }

}

class ForumMessageDataComponent extends ObjectDataComponent
{
    private $forum;

    protected function make(){
        $this->field('id',          'int',      array('request', 'unsigned', 'auto'));
        $this->field('forum_id',    'int',      array('request', 'unsigned'));
        $this->field('parent_id',   'int',      array('unsigned'));
        $this->field('user_id',     'int',      array('request', 'unsigned'));
        $this->field('text',        'string',   array('request', 'length'=>65536));
        $this->field('time',        'datetime', array('request'));
        $this->field('status',      'int',      array());

        $this->name('forum_message');

        $this->setGiver($this->component->getGiver());
    }

    public function getHtmlText(){
        return nl2br(htmlspecialchars($this->text));
    }

    public function getHumanTime(){
        return DateFormatted::humanDate($this->time);
    }

    public function setForum(ForumDataComponent $forum){
        $this->forum = $forum;
    }

    /**
     * @return ForumDataComponent
     */
    public function getForum(){
        assert(isset($this->forum));
        return $this->forum;
    }

    public function getForumUrl(){
        $giver  = $this->component->getGiver();
        $giver->setData($this->getForum());
        return $giver->defaultUrl();
    }
}
?>