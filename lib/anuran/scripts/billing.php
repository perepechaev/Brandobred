<?php

/**
 * Скрипт загрузки данных из файлов в базу данных
 *
 * Здесь несколько ключевых моментов:
 * 1. Скрипт жрет где-то 100 мб оперативной памяти,
 *    следовательно нужно позаботится о том, что
 *    php достаточно памяти.
 * 2. MYSQL_COUNT_INSERT_LIST, по умолчанию равно 100
 *    задается в config.php. По сути, чем больше, тем
 *    быстрее будет идти загрузка файлов в БД.
 *    Единственная проблема в том, что MySQL имеет
 *    ограничение на длину запроса, следовательно,
 *    при слишком высоком значении будет выдаваться
 *    ошибка
 * 3. Загрузка происходит в базу MYSQL_DBNAME
 * 4. Перед загрузкой скрипт удалит таблицы из базы
 *    данных. Чтобы этого избежать закомментируйте
 *    строку $import->clearTables();
 *    Проверки на дублирующие значения нет, при желании
 *    можно добавить параметр IGNORE в SQL запросы
 * 5. Есть множество причин, по которым загрузка может
 *    несостоятся. Вот будет обидно, когда таблицы старые
 *    уже удалим, а потом обнаружим, что файлов для
 *    загрузки у нас нет. Поэтому,
 *
 *    ПРЕЖДЕ ЧЕМ ЗАПУСКАТЬ ЭТОТ СКРИПТ, попробуйте
 *    запустить его в тестовом режиме. В этом случае
 *    он будет работать с тестовой базой данных, поэтому
 *    вероятность потери данных сводится к нулю. Если
 *    тестовая загрузка не показала ошибок, то можете
 *    смело "импортироваться по-настоящему"
 *
 *    Запуск тестового режима:
 *    script/>
 *    php test/ImportBilling/TestImport.class.php
 *
 */

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 0);

define('TESTING_RUN', false);

chdir(dirname(__FILE__) . '/../');
require_once('config.php');
require_once(PATH_CORE . '/assert.php');
require_once(PATH_MODEL . '/billing/BillingImport.class.php');


$mysql      = Mysql::instance();
$mysql->createDbIfNotExists();
$mysql->useDb();

ob_start();

    $import     = new BillingImport();
    $import->setPrefixTable('');
    $import->setImportFiles(BILLING_FILE_GROUP, BILLING_FILE_SERVICE, BILLING_FILE_BILLING);
    $import->clearTables();

    try {
       $import->go();
//        Mysql::instance()->inserts($import->goList('billing'));
    }
    catch (Exception $e){
        echo "ERROR " . get_class($e) . "({$e->getCode()}): " . mb_convert_encoding($e->getMessage(), TEST_OUTPUT_ENCODE, 'UTF-8');
    }

ob_end_clean()
?>