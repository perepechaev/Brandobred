<?php

require_once(PATH_MODEL . '/object/ObjectComponent.class.php');
require_once(PATH_MODEL . '/polling/PollingDataComponent.class.php');
require_once(PATH_MODEL . '/polling/PollingAccessorComponent.class.php');
require_once(PATH_MODEL . '/polling/PollingTemplateComponent.class.php');
require_once(PATH_MODEL . '/polling/PollingGiverComponent.class.php');

class PollingComponent extends ObjectComponent
{
    /**
     * @var DFPollingAnswerDataComponent
     */
    private $answer;
    
    /**
     * @var DFPollingUserDataComponent
     */
    private $user;
    
    /**
     * @var UserComponent
     */
    private $userComponent;
    
    /**
     * (non-PHPdoc)
     * @see model/object/ObjectComponent#getData()
     * @return DFPollingDataComponent
     */
    public function getData(){
        return parent::getData();
    }
    
    public function setAnswerData(PollingAnswerDataComponent $answer){
        $this->answer   = $answer;
    }
    
    public function setUserComponent(UserComponent $userComponent){
        $this->userComponent = $userComponent;
    }
    
    /**
     * @return UserComponent
     */
    public function getUserComponent(){
        return $this->userComponent;
    }
    
    /**
     * @return DFPollingUserDataComponent
     */
    public function getUserData(){
        equal(isset($this->user));
        return clone $this->user;
    }
    
    public function setUserData(PollingUserDataComponent $user){
        $this->user = $user;
    }
    
    /**
     * @return DFPollingAnswerDataComponent
     */
    public function getAnswerData(){
        equal(isset($this->answer));
        return clone $this->answer;
    }
    
    /**
     * Сохранить опрос
     *
     * @param   int     $id
     * @param   array   $data
     * @return  PollingDataComponent
     */
    public function save($pollId, $data){
        $mysql          = Mysql::instance();
        $poll           = $pollId ? $this->getAccessor()->getById($pollId) : $this->getData();

        // Сохраняем вопрос
        $poll->question = $data['question'];
        $poll->date     = date('Y-m-d');
        $poll->status   = ObjectComponent::STATUS_APPROVE;
        $mysql->save($poll);

        // Сохраняем ответы
        $list           = $this->getData()->makeAnswer()->createList();
        foreach ($data['answers'] as $id => $text){
            $answer         = $poll->makeAnswer();
            $answer->poll_id= $poll->id;
            $answer->count  = 0;
            if (preg_match('/^id_(\d+)$/', $id, $match)){
                $answer->id     = $match[1];
                $answer         = $this->getAccessor()->getAnswerById($answer->id);
                if (!$text){
                    $mysql->delete($answer);
                }
            }

            $answer->answer = $text;
            if ($text){
                equal($poll->id == $answer->poll_id, "{$poll->id} !== {$answer->poll_id}");
                $mysql->save($answer);
                $list->add($answer);
            }
        }
        $poll->setAnswers($list);
        return $poll;
    }

    /**
     * @return PollingComponent
     */
    static public function create(){
        $component  = new self();
        $component->setGiver(    new PollingGiverComponent($component) );
        $component->setData(     new PollingDataComponent($component) );
        $component->setAccessor( new PollingAccessorComponent($component) );
        
        $answer     = new PollingAnswerDataComponent($component);
        $answer->setGiver( new PollingAnswerGiverComponent($component) );
        $accessor   = new ObjectAccessorComponent($component);
        $accessor->setData($answer);
        $answer->setAccessor($accessor);
        $component->setAnswerData($answer);
        
        $user       = new PollingUserDataComponent($component);
        $user->setGiver( new ObjectGiverComponent($component) );
        $accessor   = new ObjectAccessorComponent($component);
        $accessor->setData($user);
        $user->setAccessor($accessor);
        $component->setUserData($user);
        
        $component->setList(     new ObjectListComponent() );
        $component->setTemplate( new PollingTemplateComponent() );
        
        return $component;
    }
}


?>