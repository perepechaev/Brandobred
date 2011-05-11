<?php

class ObjectGiverComponent
{
    /**
     * @var ObjectComponent
     */
    protected $contr;

    /**
     * @var ObjectDataComponent
     */
    protected $data;

    final public function __construct(ObjectComponent $obj){
        $this->contr        = $obj;
    }

    final public function setData(ObjectDataComponent $obj){
        $this->data         = $obj;
    }

    final public function _is($name){
        return method_exists($this, $name);
    }

    final public function _get($name){
        return $this->$name();
    }

    /**
     * @return ObjectComponent
     */
    protected function getComponent(){
        return $this->contr;
    }
    
    public function statusList(){
        return array(
            ObjectComponent::STATUS_UNPROVEN,
            ObjectComponent::STATUS_APPROVE,
            ObjectComponent::STATUS_DISAPPROVE,
            ObjectComponent::STATUS_DELETE,
        );
    }
    
    public function statusSelect(){
        return array(
            'current' => $this->data->status,
            'list'    => $this->statusList()
        );
    }
    
    public function this(){
        return $this->data;
    }
    
    public function destroy(){
        unset($this->data);
        unset($this->contr);
    }
}


?>