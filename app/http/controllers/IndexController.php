<?php

class IndexController extends Controller {
    public function initContent()
    {
        // Pray the god we don't even need a front door!
        Tools::redirect($this->context->link->getPageLink('comments'));
    }
}