<?php

class AuthController extends Controller {
    // Ce controller doit être accessible hors connexion
    public $access_restriction = false;

    public function initContent()
    {
        parent::initContent();
        $this->setTemplate('auth.tpl');
    }

    public function setMedias()
    {
        parent::setMedias();
    }

    public function postProcess()
    {
        parent::postProcess();
    }
}