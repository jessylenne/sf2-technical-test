<?php

class Autoloader {
    function autoload($class) {
        if(class_exists($class))
            return true;

        $classes = $this->getClassesFromDir();
        if(isset($classes[$class])) {
            require_once($classes[$class]);
            return $classes[$class];
        }

        return false;
    }

    /**
     * Recrusivité sur un repertoire afin d'en retirer le chemin de chaque classe
     * @param string $path
     * @return array
     */
    function getClassesFromDir($path = "app") {
        $classes = array();

        $path = rtrim($path, DS).DS;

        foreach (scandir(_ROOT_PATH_.DS.$path.DS) as $file) {
            if ($file[0] != '.') {
                if (is_dir(_ROOT_PATH_.DS.$path.$file)) {
                    $classes = array_merge($classes, $this->getClassesFromDir($path.$file.DS));
                } elseif (substr($file, -4) == '.php') {
                    $classes[substr($file, 0, strlen($file) - 4)] = _ROOT_PATH_.DS.$path.$file;
                }
            }
        }

        return $classes;
    }
}


spl_autoload_register(array(new Autoloader, 'autoload'));