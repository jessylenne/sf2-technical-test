<?php

class Context {
    private static $_instance;
    private static $_instanciable = false;

    /**
     * @var Smarty
     */
    public $smarty;

    public $user;
    /**
     * @var Link
     */
    public $link;
    /**
     * @var Cookie
     */
    public $cookie;

    public function __construct() {
        if(!self::$_instanciable)
            throw new Exception("You may use Context::getContext()");

        $this->initialize();
    }

    /**
     * Singleton
     * @return Context
     */
    public static function getContext()
    {
        if(!self::$_instance) {
            self::$_instanciable = true;
            self::$_instance = new self();
            self::$_instanciable = false;
        }

        return self::$_instance;
    }

    private function initialize() {
        $this->initializeDefaults();
        $this->initializeSmarty();
    }

    /**
     * Initialize default public variables
     */
    private function initializeDefaults()
    {
        $this->cookie = new Cookie(Env::get('cookie_name', 'cookiemonster'));
        $this->link = new Link();
    }

    /**
     * Initialize view motor
     */
    public function initializeSmarty()
    {
        $this->smarty = new Smarty();
        $this->smarty->setCompileDir(_CACHE_DIR_.DS.'smarty'.DS.'compile');
        $this->smarty->setCacheDir(_CACHE_DIR_.DS.'smarty'.DS.'cache');
        $this->smarty->force_compile = Env::get('smarty.force_compile');
        $this->smarty->compile_check = Env::get('smarty.compile_check');

        // Assignation des variables utiles
        $this->smarty->assign('link', $this->link);
        $this->smarty->assign('tpl_dir', _VIEW_PATH_);
        $this->smarty->assign('base_uri', _BASE_URI_);
        $this->smarty->assign('app', Env::get('app'));
    }
}