<?php

trait AuthenticatedController {
    private function accessRestriction()
    {
        if($this->access_restriction && !Auth::getUser())
            Tools::redirect($this->context->link->getPageLink('auth'));

        $this->smarty->assign('user', Auth::getUser());
    }
}