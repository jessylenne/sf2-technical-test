<?php

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'init.php');

if(Env::get('database.driver') !== 'sqlite')
    exit;

unlink(dirname(__FILE__).DS.'database.sqlite');
$cm = @file_get_contents(_ROOT_PATH_.DS.'install.sqlitectn');
$cm = explode(';', $cm);
foreach($cm as $q) {
    Db::getInstance()->Execute($q);
    echo Db::getInstance()->getMsgError()."\n";
}

echo "done";