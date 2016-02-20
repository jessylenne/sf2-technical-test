<?php

class Controller {
    // Obligation d'être connécté pour accéder à l'intégralité de l'app
    use AuthenticatedController;

    public $meta_title;
    /**
     * @var Context
     */
    public $context;

    /**
     * @var Smarty
     */
    public $smarty;

    /**
     * Is this page restricted to authenticated users?
     * @bool
     */
    public $access_restriction = true;

    private $_CSS_FILES = array();
    private $_JS_FILES = array();

    /** @var array Messages de succès à afficher dans la page en cours */
    public $success = array();

    /** @var array Messages d'erreur à afficher dans la page en cours */
    public $errors = array();

    private $_template = 'index.tpl';

    public function __construct() {
        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
    }

    public function run()
    {
        try {
            $this->init();
            $this->postProcess();
        }
        catch(Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        $this->display();
    }

    public function init()
    {
        $this->accessRestriction();
        $this->initHeader();
        $this->initContent();
    }

    /**
     * initialise header (assets, metas, ...)
     */
    public function initHeader()
    {
        $this->setMedias();
        $this->setMetas();
    }

    /**
     * Add assets (CSS & JS)
     */
    public function setMedias()
    {
        if(file_exists(_CSS_PATH_.'app.css'))
            $this->addCSS(_CSS_DIR_.'app.css');

        if(file_exists(_JS_PATH_.'all.js'))
            $this->addJS(_JS_DIR_.'all.js');

        $this->smarty->assign('css_files', $this->_CSS_FILES);
        $this->smarty->assign('js_files', $this->_JS_FILES);
    }

    public function addCSS($filePath) {
        if(!in_array($filePath, $this->_CSS_FILES))
            $this->_CSS_FILES[] = $filePath;
    }

    public function addJS($filePath) {
        if(!in_array($filePath, $this->_JS_FILES))
            $this->_JS_FILES[] = $filePath;
    }

    /**
     * Assign titles, description, keywords, ...
     */
    public function setMetas()
    {
        $this->smarty->assign('meta_title', strlen($this->meta_title) ? $this->meta_title : Env::get('app.name'));
    }

    /**
     * Preload and assign datas
     */
    public function initContent()
    {
    }

    /**
     * Process forms
     */
    public function postProcess()
    {

    }

    public function display()
    {
        $this->assignMessages();

        $this->smarty->assign('display_header', !Tools::getIsset('content_only'));
        $this->smarty->assign('display_footer', !Tools::getIsset('content_only'));
        $this->smarty->assign('template', $this->smarty->fetch(_VIEW_PATH_.$this->getTemplate()));

        $this->smarty->display(_VIEW_PATH_.$this->getLayout());
    }

    public function getTemplate() {
        return $this->_template;
    }

    /**
     * @param $template
     * @return String
     */
    public function setTemplate($template) {
        return $this->_template = $template;
    }

    public function getLayout() {
        return 'layouts'.DS.'default.tpl';
    }

    private function assignMessages()
    {
        $errors = array_merge($this->errors, Flash::getMessages(Flash::TYPE_ERROR, true));
        $success = array_merge($this->success, Flash::getMessages(Flash::TYPE_SUCCESS, true));
        Flash::getMessages(null, true);

        $this->smarty->assign('errors', $errors);
        $this->smarty->assign('success', $success);
    }
}