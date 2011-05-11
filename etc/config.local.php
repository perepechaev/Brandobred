<?php
error_reporting(E_ALL | E_STRICT);

// Стандартные страницы
define('PAGE_NOT_FOUND',            'notfound.php');

// Загрузка файлов
define('MAX_UPLOAD_FILE_SIZE',      2097152);   // 2Mb
define('DETAIL_UPLOAD_ERROR',       true);
define('FILESYSTEM_ENCODE',         'UTF-8');  // Кодировка для файловой системы ОС
define('UPLOAD_DETECT_ENCODE',      'auto, Windows-1251'); // Список допустимых кодировок

// Пользователи
define('USER_NAME_MAX_LENGTH',      20);        // Максимальная длина имени пользователя
define('USER_NAME_MIN_LENGTH',      3);
define('USER_PWD_MAX_LENGTH',       32);        // Максимальная длина пароля пользователя
define('USER_PWD_SALT',             '');

// Файлы
define('FILE_CACHE_TIME_ALL',       9000);       // Достаточно оставить такими большими,
define('FILE_CACHE_TIME_USER',      18000);      // но лучше запускать еще по крону несколько раз в день
define('FILE_CACHE_PATH',           PATH_ETC . '/file');
define('FILE_COUNT_PER_PAGE',       10);       // Количество файлов на одной странице
define('FILE_ERROR_LOG_FILE',       PATH_ETC . '/logs/files.error');

// Mysql
define('MYSQL_HOST',                'localhost');
define('MYSQL_DBNAME',              'brandtag');
define('MYSQL_USER',                'root');
define('MYSQL_PWD',                 '');
define('MYSQL_ENCODE',              'UTF8');
define('MYSQL_TABLE_PREFIX',        '');
define('MYSQL_COUNT_INSERT_LIST',   100);
define('MYSQL_LOG',                 true);

// Настройка структуры сайта
define('URL_SITE',                  'brandomet.ru');  // Без слэша на конце
define('URL_PATH',                  '/');

// Почта
define('MAIL_ENCODE',               'UTF-8');
define('MAIL_ADDR_NOREPLY',         'perepechaev@inbox.ru');
define('MAIL_MODERATOR',            'perepechaev@inbox.ru');

// Twitter.com
define("TWITTER_KEY",               '5W9Wtaz2tsbNgkdezIoBTg');
define("TWITTER_SECRET",            'e8SBd0g3mFRqrW3BHxQEOm5gAgvOIleWb1CI9ImqhAo');

// Facebook.com
define('FACEBOOK_ID',               357469619622);
define('FACEBOOK_KEY',              '95d92b17b9e7b1f5d9ef26949e6334f3');
define('FACEBOOK_SECRET',           'c2353a791ddafce9e1320cc2c91a5cdb');

// Тестирование
define('TEST_OUTPUT_ENCODE',        'UTF-8');   // UTF-8 для HTTP, CP866 для Win-консоли
define('TEST_HTTP_PWD',             'something'); // URL_HTTP/test.php?something=simple
define('TEST_DBNAME',               'test');// БД для тестов, чтобы случайно нужную информацию не затереть

// Отладка
define('DEBUG_SHOW_EXCEPTION', true);
define('DEBUG_SIMPLE_AUTHORIZE', false);
define('DEBUG_SHOW_SESSION', true);
define('DEBUG_SHOW_SQL_QUERY', false);



?>
