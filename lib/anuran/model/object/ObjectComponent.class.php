<?php

require_once(PATH_MODEL . '/object/ObjectDataComponent.class.php');
require_once(PATH_MODEL . '/object/ObjectListComponent.class.php');
require_once(PATH_MODEL . '/object/ObjectAccessorComponent.class.php');
require_once(PATH_MODEL . '/object/ObjectGiverComponent.class.php');
require_once(PATH_MODEL . '/object/ObjectCriteriaComponent.class.php');

class ObjectComponent
{
	const STATUS_UNPROVEN	= 0;
	const STATUS_APPROVE	= 1;
	const STATUS_DISAPPROVE	= 2;
	const STATUS_DELETE		= 3;

    /**
     * @var ObjectDataComponent
     */
    private $objData                    = null;

    /**
     * @var ObjectGiverComponent
     */
    private $objGiver                   = null;

    /**
     * @var ObjectListComponent
     */
    private $objList                    = null;

    /**
     * @var ObjectAccessorComponent
     */
    private $objAccessor                = null;

    /**
     * @var Controller
     */
    private $objController              = null;

    /**
     * @var Template
     */
    private $objTemplate                = null;

    protected function __construct(){
        $this->objGiver     = new ObjectGiverComponent($this);
        $this->objData      = new ObjectDataComponent($this);
        $this->objAccessor  = new ObjectAccessorComponent($this);
        $this->objList      = new ObjectListComponent();
    }

    public function setData(ObjectDataComponent $obj){
        $this->objData      = $obj;
        $this->objData->setGiver($this->getGiver());
    }

    public function setList(ObjectListComponent $obj){
        $this->objList      = $obj;
    }

    public function setGiver(ObjectGiverComponent $obj){
        $this->objGiver     = $obj;
    }

    public function setAccessor(ObjectAccessorComponent $obj){
        $this->objAccessor  = $obj;
    }

    public function setController(Controller $obj){
        $this->objController= $obj;
    }

    public function setTemplate(Template $obj){
        $this->objTemplate  = $obj;
    }

    /**
     * @return ObjectDataComponent
     */
    public function getData(){
        $data       = clone $this->objData;
        $giver      = $this->getGiver();
        $data->setGiver($giver);
        $giver->setData($data);

        return $data;
    }

    /**
     * @return ObjectListComponent
     */
    public function getList(){
        return clone $this->objList;
    }

    /**
     * @return ObjectGiverComponent
     */
    public function getGiver(){
        $giver  = clone $this->objGiver;
        return $giver;
    }

    /**
     * @return ObjectAccessorComponent
     */
    public function getAccessor(){
        return clone $this->objAccessor;
    }

    /**
     * @return Controller
     */
    public function getController(){
        return $this->objController;
    }

    /**
     * @return Template
     */
    public function getTemplate(){
        equal(isset($this->objTemplate), "Не установлен шаблон");
        return $this->objTemplate;
    }

    public function getHtmlControl(ObjectDataComponent $data){
        $contr  = clone $this->objController;
        $contr->control($data);
        return $contr->getHtml();
    }

    /**
     * @return ObjectComponent
     */
    static public function create(){
        return new ObjectComponent();
    }
}


?>