<?php

require_once(PATH_MODEL . '/billing/Billing.class.php');

class BillingImport
{
    private $tables = array();
    private $lists  = array();
    private $files  = array();


    /**
     * @var Mysql
     */
    private $mysql;

    public function __construct(){
        $this->tables   = array(
            'group'     => new ServiceGroup(),
            'service'   => new Service(),
            'billing'   => new Billing(),
        );

        foreach ($this->tables as $key => $value){
            $this->lists[$key]  = new BillingList();
            $this->lists[$key]->setCallback(array($value, 'loadFromData'));
        }

        $this->mysql    = Mysql::instance('BillingImport');
    }

    public function setImportFiles($fileGroup, $fileService, $fileBilling){
        $this->files    = array(
            'group'     => $fileGroup,
            'service'   => $fileService,
            'billing'   => $fileBilling,
        );
        foreach ($this->lists as $key => $item){
            $item->setFileName($this->files[$key]);
        }
    }

    public function setPrefixTable($prefix){
        $this->mysql->setTablePrefix($prefix);
    }

    public function clearTables(){
        foreach ($this->tables as $table) {
            if ($this->mysql->isTableExists($table)){
                $this->mysql->dropTable($table);
            }
            $this->mysql->createTable($table);
        }
    }

    public function go(){
        $this->mysql->inserts( $this->goList('group')   );
        $this->mysql->inserts( $this->goList('service') );
        $this->mysql->inserts( $this->goList('billing') );
    }

    /**
     * @param string $listName
     * @return BillingList
     */
    public function goList($listName){
        $this->lists[$listName]->loadFromFile();
        return $this->lists[$listName];
    }
}

?>