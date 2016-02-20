<?php

class Env {
    private static $_instance;
    private $_env;

    public function __construct($envFilePath = null) {
        if(!$envFilePath)
            $envFilePath = _ROOT_PATH_.DS.".env";

        if(!file_exists($envFilePath))
            throw new Exception("Unable to find .env at ".$envFilePath);

        $this->_env = parse_ini_file($envFilePath, true);
    }

    public static function getInstance() {
        if(!self::$_instance)
            self::$_instance = new self();

        return self::$_instance;
    }

    public static function get($key, $default = false)
    {
        $exists = self::getInstance()->getValue($key);
        return $exists ? $exists : $default;
    }

    public function getValue($key) {
        if((is_array($key) && !sizeof($key)) || (!is_array($key) && !strlen($key)))
            throw new Exception('Bad env key format');

        $key = explode('.', $key);
        $curr = $this->_env;
        foreach($key as $k) {
            if(!isset($curr[$k]))
                return false;

            $curr = $curr[$k];
        }

        return $curr;
    }
}