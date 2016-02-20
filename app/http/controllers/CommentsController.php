<?php

class CommentsController extends Controller {
    public function initContent()
    {
        parent::initContent();
        $this->setTemplate('comments/index.tpl');

        // On consulte un profil utilisateur
        if(Tools::getIsset('user') && Validate::isNonEmptyString($username = Tools::getValue('user'))) {
            try {
                $profile = Github::getUser($username);
                if(!$profile || !isset($profile['login']))
                    $this->errors[] = 'Utilisateur introuvable';
                else {
                    // et les commentaires que l'on a déposé sur ce profil
                    $comments = Auth::getUser()->comments(array('username' => $username));

                    $this->smarty->assign(compact('profile', 'comments'));
                }

                $this->setTemplate('comments/profile.tpl');
            }
            catch(Exception $e) {
                $this->errors[] = 'Impossible de récupérer l\'utilisateur et ses dépôts';
            }
        }
        // Index des commentaires
        else {
            // Affichage des derniers commentaires saisis
            $comments = Auth::getUser()->comments(array('limit' => 15));
            $this->context->smarty->assign(compact('comments'));
        }
    }

    public function postProcess()
    {
        // Recherche d'un utilisateur
        if(Tools::isSubmit('submitSearchAccount')) {
            $this->setTemplate('comments/search.tpl');

            try {
                $accounts = Github::searchAccounts(Tools::getValue('search'));

                $this->context->smarty->assign(array(
                    'total_count'   => $accounts['total_count'],
                    'accounts'      => $accounts['items']
                ));
            }
            catch(Exception $e) {
                $this->errors[] = 'Impossible de récupérer les profils correspondant à votre recherche';
            }
        }
    }
}