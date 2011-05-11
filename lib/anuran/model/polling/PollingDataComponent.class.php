<?php

require_once(dirname(__FILE__) . '/PollingComponentException.class.php');
//require_once (PATH_MODEL . '/image/IImageObject.class.php');

class PollingDataComponent extends ObjectDataComponent
{
    /**
     * @var MysqlData
     */
    private $user           = null;

    protected function make(){
        parent::make();
        $this->refield('title',       'string',   array('request', 'unique'));
        $this->alias('title', 'question');
        
        $this->name('polling');
    }
    
    public function __set($key, $value){
        PollingComponentException::create()->preventChange($this);
    }
    
    /**
     * Проголосовать в опросе.
     *
     * Указывается id-вопроса, id-ответа, ip-пользователья и
     * информация о браузере пользователя
     *
     * @param   int  $user_id
     * @param   int  $answ_id
     * @return  DFPollingUserDataComponent
     */
    public function poll($user_id, $answ_id){
        PollingComponentException::create()->objectInstance($this);
        
        $answer             = $this->getComponent()->getAnswerData()->getAccessor()->getById($answ_id);
        equal($this->id === $answer->poll_id);
        
        return $answer->poll($user_id);
    }
    
    public function mass(UserDataComponent $user, $answer_ids){
        PollingComponentException::create()->objectInstance($this);
        PollingComponentException::create()->objectInstance($user);
        
        $vote       = $this->getComponent()->getUserData();
        $votes      = $vote->createList();
        $answers    = $this->answers;
        foreach ($answer_ids as $answer_id){
            if (!$answers->is($answer_id)){
                PollingComponentException::create()->wrongAnswerIdForPolling($answer_id);
            }
            $answer = $answers->get($answer_id);
            $votes->add( $answer->makeVote( $user ));
        }
        
        $votes->insert();
        $answers->save();
    }

    public function getImagePath(){
        return 'polling/';
    }
    
    public function imageSave(ImageData $image){
        $image->thumbnail( $this );
        $image->save();
    }
    
    
    /**
     * Создать чистый опрос
     * 
     * @param $title string Название опроса
     * @return DFPollingDataComponent
     */
    public function create($title){
        PollingComponentException::create()->objectNotInstance($this);
        
        parent::__set('question', $title);
        parent::__set('status', ObjectComponent::STATUS_APPROVE);
        
        $this->save();
        return $this;
    }
    
    /**
     * @param $answer string вариант ответа
     * @return DFPollingDataComponent
     */
    public function addAnswer(){
        PollingComponentException::create()->objectInstance($this);
        
        $answer = $this->getComponent()->getAnswerData();
        if (func_num_args() === 1 && is_array(func_get_arg(0))){
            $values = func_get_arg(0);
        }
        else{
            $values = func_get_args();
        }
        foreach ($values as $value){
            $answer->clean();
            $answer->create($this, $value);
        }
        return $this;
    }
    
    public function rename($title){
        equal(is_string($title));
        PollingComponentException::create()->objectInstance($this);
        
        $title  = trim($title);
        if ($title === $this->question){
            return $this;
        }
        
        parent::__set('question', $title);
        $this->save();
        return $this;
    }
    
    /**
     * @return PollingComponent
     */
    public function getComponent(){
        return $this->component;
    }

}

class PollingUserDataComponent extends ObjectDataComponent
{
    protected function make(){
        $this->field('id',          'int',      array('request', 'unsigned', 'auto'));
        $this->field('answer_id',   'int');
        $this->field('user_id',     'string',   array('request', 'length'=>15));
        $this->field('date',        'datetime');

        $this->name('polling_user');
    }
}

class PollingAnswerDataComponent extends ObjectDataComponent
{
    protected function make(){
        $this->field('id',          'int',      array('request', 'unsigned', 'auto'));
        $this->field('poll_id',     'int',      array('request', 'unsigned'));
        $this->field('answer',      'string',   array('request'));
        $this->field('count',       'int',      array('request', 'default' => 0));
        
        $this->alias('answer', 'title');

        $this->name('polling_answer');
    }
    
    public function __set($key, $value){
        PollingComponentException::create()->preventChange($this);
    }
    
    /**
     * Создать вариант ответа для голосования 
     * 
     * @param $polling  PollingDataComponent    Опрос
     * @param $text     string                  Вариант ответа в опросе
     * @return DFPollingAnswerData
     */
    public function create(PollingDataComponent $polling, $text){
        equal(is_string($text), var_export($text, true));
        PollingComponentException::create()->objectNotInstance($this);
        PollingComponentException::create()->objectInstance($polling);
        
        $text = trim($text);
        if (empty($text)){
            return $this;
        }
        
        parent::__set('poll_id', $polling->id);
        parent::__set('answer',  $text);
        
        $this->save();
        return $this;
    }
    
    /**
     * (non-PHPdoc)
     * @see model/object/ObjectDataComponent#make()
     * @return DFPollingUserDataComponent
     */
    public function makeVote(UserDataComponent $user){
        PollingComponentException::create()->objectInstance($this);
        PollingComponentException::create()->objectInstance($user);
        
        $vote               = $this->getComponent()->getUserData();
        $vote->answer_id    = $this->id;
        $vote->user_id      = $user->id;
        $vote->date         = date('Y-m-d H:i:s');
        
        parent::__set('count', $this->count + 1);
                
        return $vote;
    }
    
    public function poll($user_id){
        $user               = $this->getComponent()->getUserData();
        $user->answer_id    = $this->id;
        $user->user_id      = $user_id;
        $user->date         = date('Y-m-d H:i:s');
        try {
            Mysql::instance()->start_transaction();
            $user->save();
            
            parent::__set('count', $this->count + 1);
            $this->save();
            
            Mysql::instance()->commit_transaction();
        }
        catch (Exception $e){
            Mysql::instance()->revert_transaction();
            throw $e;
        }

        return $user;
    }
    
    /** (non-PHPdoc)
     * @see model/object/ObjectDataComponent#getComponent()
     * @return PollingComponent
     */
    public function getComponent(){
        return parent::getComponent();
    }

}