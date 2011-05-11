<?php

require_once(dirname(__FILE__) . '/../TestHead.php');
require_once(PATH_CORE . '/Mysql.class.php');
require_once(PATH_MODEL . '/billing/BillingImport.class.php');

class TestImportBilling extends Test
{
    protected $detail       = false;
    protected $marktime     = true;
    protected $useBuffer    = false;

    public function test_constructor(){
//        $this->detail   = true;
        $this->result('Start import', 'ok');
        $mysql      = Mysql::instance();
        $mysql->createDbIfNotExists();
        $mysql->useDb();
        $mysql->setTablePrefix('import_test_');


        $import     = new BillingImport();
        $import->setPrefixTable('import_test_');
        $import->setImportFiles(BILLING_FILE_GROUP, BILLING_FILE_SERVICE, BILLING_FILE_BILLING);
        $import->clearTables();
        $this->result('Clear table', 'ok');

        $group      = $import->goList('group');
        $this->result('Loaded group', $group->count());

        $service     = $import->goList('service');
        $this->result('Loaded service', $service->count());

        $billing     = $import->goList('billing');
        $this->result('Loaded billing', $billing->count());

        $mysql->inserts($group);
        $this->result('Groups is stroed in DB', 'ok');

        $mysql->inserts($service);
        $this->result('Services is stroed in DB', 'ok');


        $mysql->inserts($billing);
        $this->result('Billing is stroed in DB', 'ok');
    }

}

// XXX: Больше не требуется
//$test   = new TestImportBilling();
//$test->complete();

?>