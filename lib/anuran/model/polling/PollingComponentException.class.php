<?php

require_once(PATH_MODEL . '/object/ObjectComponentException.class.php');

class PollingComponentException extends ObjectComponentException
{
    const WRONG_ANSWER_ID_FOR_POLLING   = 101;
    
    public function wrongAnswerIdForPolling($answer_id){
        $this->execute('Неверный вариант ответа для голосования. Голосовать можно в пределеах одного опроса: answer_id = ' . $answer_id, self::WRONG_ANSWER_ID_FOR_POLLING);
    }
    
    /**
     * @return PollingComponentException
     */
    static public function create(){
        return new PollingComponentException();
    }
}

?>