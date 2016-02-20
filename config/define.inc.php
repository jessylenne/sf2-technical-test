<?php

/** Constantes */
define('DS', DIRECTORY_SEPARATOR);

/** Local directories */
define('_VIEW_PATH_', _ROOT_PATH_.DS."ressources".DS."views".DS);
define('_RESSOURCES_PATH_',_ROOT_PATH_.DS."ressources".DS);
define('_CACHE_DIR_', _ROOT_PATH_.DS."cache");
define('_PUBLIC_PATH_', _ROOT_PATH_.DS."public".DS);
define('_ASSETS_PATH_',_PUBLIC_PATH_."assets".DS);
define('_CSS_PATH_', _ASSETS_PATH_."css".DS);
define('_JS_PATH_', _ASSETS_PATH_."js".DS);

/** URI **/
define('_BASE_URI_', rtrim(Env::get('app.base_uri', ""), "/")."/");
define('_ASSETS_DIR_', _BASE_URI_."assets/");
define('_CSS_DIR_', _ASSETS_DIR_."css/");
define('_JS_DIR_', _ASSETS_DIR_."js/");