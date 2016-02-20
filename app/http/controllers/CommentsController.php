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

                    // On récupère ses dépots
                    $repositories = Github::getUserRepositories($username);
                    if(is_array($repositories) && sizeof($repositories))
                        $this->profile['repositories'] = $repositories;

                    $this->smarty->assign(compact('profile', 'comments', 'repositories'));
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
            elseif(!Validate::isNonEmptyString($repository = Tools::getValue('repository')))
                return $this->errors[] = 'Veuillez specifier un nom de dépot valide';
            elseif(!Validate::isCleanHTML($commentContent = Tools::getValue('comment')))
                return $this->errors[] = 'Veuillez fournir un commentaire valide';
            elseif(!$this->isProfileRepository($repository))
                return $this->errors[] = "Ce dépot n'appartient pas à cet utilisateur";

            $comment = new Comment();
            $comment->setUser(Auth::getUser())
                ->setUsername($this->profile['login'])
                ->setRepository($repository)
                ->setComment(nl2br($commentContent)); // @todo WYSIWYG

            if(!$comment->save()) {
                $this->errors[] = "Impossible d'enregistrer le commentaire (".Db::getInstance()->getMsgError().")";
            }
            else {
                Flash::success('Votre commentaire a bien été ajouté!');
                Tools::redirect($this->context->link->getPageLink('comments', array('user' => $this->profile['login'])));
            }
        }
    }

    /**
     * Check if given repository is owned by our profile
     * @param $repositoryName
     * @return bool
     * @internal param $repository
     */
    private function isProfileRepository($repositoryName)
    {
        if($repositoryName == 'all') return true;

        if($this->profile && Validate::isNonEmptyArray($this->profile['repositories'])) {
            foreach($this->profile['repositories'] as $repository) {
                if($repository['name'] == $repositoryName)
                    return true;
            }
        }

        return false;
    }
}