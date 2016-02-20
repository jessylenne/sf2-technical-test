<?php

smartyRegisterFunction(Context::getContext()->smarty, 'modifier', 'text', 'smartyText');
smartyRegisterFunction(Context::getContext()->smarty, 'modifier', 'sanitize', 'smartySanitize');

function smartyText($data)
{
    // Prevent xss injection.
    if (Validate::isCleanHtml($data)) {
        return stripslashes(preg_replace('/\v+|\\\[rn]/','<br/>', $data));
    }

    return '';
}

function smartySanitize($data)
{
    return htmlentities($data);
}

function smartyRegisterFunction($smarty, $type, $function, $params, $lazy = true)
{
    if (!in_array($type, array('function', 'modifier', 'block'))) {
        return false;
    }

    // lazy is better if the function is not called on every page
    if ($lazy) {
        $lazy_register = SmartyLazyRegister::getInstance();
        $lazy_register->register($params);

        if (is_array($params)) {
            $params = $params[1];
        }

        // SmartyLazyRegister allows to only load external class when they are needed
        $smarty->registerPlugin($type, $function, array($lazy_register, $params));
    } else {
        $smarty->registerPlugin($type, $function, $params);
    }
}


/**
 * Used to delay loading of external classes with smarty->register_plugin
 */
class SmartyLazyRegister
{
    protected $registry = array();
    protected static $instance;

    /**
     * Register a function or method to be dynamically called later
     * @param string|array $params function name or array(object name, method name)
     */
    public function register($params)
    {
        if (is_array($params)) {
            $this->registry[$params[1]] = $params;
        } else {
            $this->registry[$params] = $params;
        }
    }

    /**
     * Dynamically call static function or method
     *
     * @param string $name function name
     * @param mixed $arguments function argument
     * @return mixed function return
     */
    public function __call($name, $arguments)
    {
        $item = $this->registry[$name];

        // case 1: call to static method - case 2 : call to static function
        if (is_array($item[1])) {
            return call_user_func_array($item[1].'::'.$item[0], array($arguments[0], &$arguments[1]));
        } else {
            $args = array();

            foreach ($arguments as $a => $argument) {
                if ($a == 0) {
                    $args[] = $arguments[0];
                } else {
                    $args[] = &$arguments[$a];
                }
            }

            return call_user_func_array($item, $args);
        }
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new SmartyLazyRegister();
        }
        return self::$instance;
    }
}
