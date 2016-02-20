<?php

class CommentsController extends Controller {
    private $profile;

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
                    $this->profile = $profile;
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
        // Ajout d'un nouveau commentaire
        elseif(Tools::isSubmit('submitAddComment')) {
            if(!$this->profile)
                return $this->errors[] = 'Veuillez specifier un profil valide';
            elseif(!Validate::isCleanHTML($commentContent = Tools::getValue('comment')))
                return $this->errors[] = 'Veuillez fournir un commentaire valide';

            $comment = new Comment();
            $comment->setUser(Auth::getUser())
                ->setUsername($this->profile['login'])
                ->setRepository('all')
                ->setComment(nl2br($commentContent));

            if(!$comment->save()) {
                $this->errors[] = "Impossible d'enregistrer le commentaire (".Db::getInstance()->getMsgError().")";
            }
            else {
                Flash::success('Votre commentaire a bien été ajouté!');
                Tools::redirect($this->context->link->getPageLink('comments', array('user' => $this->profile['login'])));
            }
        }
    }
}