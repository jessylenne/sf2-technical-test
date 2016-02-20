<?php

// Constantes obligatoires
define('_ROOT_PATH_', dirname(__FILE__));
define('_CONFIG_PATH_', _ROOT_PATH_."/config");

// Définitions
require_once(_CONFIG_PATH_."/functions.inc.php");
require_once(_CONFIG_PATH_."/autoload.inc.php");

// Autoload composer
if(file_exists(_ROOT_PATH_."/vendor/autoload.php"))
    @require_once(_ROOT_PATH_."/vendor/autoload.php");

require_once(_CONFIG_PATH_."/define.inc.php");

// Configuration des outils
require_once(_CONFIG_PATH_."/smarty.inc.php");