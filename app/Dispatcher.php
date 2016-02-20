<?php

class Dispatcher {
    private static $instance;

    public function createURL($route_id, array $params = array(), $anchor = '')
    {
        $params['controller'] = $route_id;

        $query = http_build_query($params, '', '&');
        $url = 'index.php?'.$query;

        return $url.$anchor;
    }

    /**
     * Get current instance of dispatcher (singleton)
     *
     * @return Dispatcher
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Dispatcher();
        }
        return self::$instance;
    }

    /**
     * Find the right controller and run it
     */
    public static function dispatch() {
        if(!$controller = self::getController())
            $controller = new PageNotFoundController();

        $controller->run();
    }

    /**
     * Return the right controller (?controller=|PageNotFound)
     * @return bool|Controller
     */
    public static function getController()
    {
        $class = ucfirst(Tools::getValue('controller', 'index')).'Controller';
        if(!class_exists($class))
            return false;

        return new $class;
    }
}