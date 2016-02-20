<?php

class CommentsController extends Controller {
    public function initContent()
    {
        parent::initContent();
        $this->setTemplate('comments/index.tpl');

        $comments = Auth::getUser()->comments(array('limit' => 15));
        $this->context->smarty->assign(compact('comments'));
    }

    public function postProcess()
    {
        // Recherche d'un utilisateur
        if(Tools::isSubmit('submitSearchAccount')) {
            $this->setTemplate('comments/search.tpl');

            try {

            }
            catch(Exception $e) {
                $this->errors[] = 'Impossible de récupérer les profils correspondant à votre recherche';
            }
        }
    }
}