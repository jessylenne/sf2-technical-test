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

        // Déconnexion
        if(Tools::getIsset('logout')) {
            Auth::disconnect();
            Flash::add('Vous êtes bien déconnécté');
            Tools::redirect($this->context->link->getPageLink('auth'));
        }
        // Soumission connexion
        elseif(Tools::isSubmit('submitLogin')) {
            $user = (new User)->getByEmail(Tools::getValue('username'), Tools::getValue('password'));
            if(!Validate::isLoadedObject($user)) {
                $this->errors[] = 'Identifiant ou mot de passe incorrect';
            }
            else {
                Auth::setUser($user);
                Tools::redirect($this->context->link->getPageLink('comments'));
            }
        }
        // Soumission inscription
        elseif(Tools::isSubmit('submitSubscribe')) {
            /**
             * - Vérification des champs
             * - Verification non-existant
             * - Inscription
             * - Login
             */
            if(!Validate::isEmail($email = Tools::getValue('username')))
                return $this->errors[] = 'Veuillez saisir une adresse e-mail correcte';

            if(!Validate::isPasswd($password = Tools::getValue('password')))
                /// @todo être plus spécifique sur les règles de mot de passes valides
                return $this->errors[] = 'Veuillez saisir un mot de passe correct';

            $user = new User();
            if(Validate::isLoadedObject($user->getByEmail($email))) {
                $this->errors[] = 'Un compte avec cet identifiant existe déjà';
            }
            else {
                $user->login = $email;
                $user->password = Tools::encrypt($password);
                if(!$user->save())
                    $this->errors[] = 'Impossible de vous enregistrer, veuillez réessayer ultérieurement ('.Db::getInstance()->getMsgError().')';
                else {
                    Auth::setUser($user);
                    Flash::success('Bienvenue! Votre compte a bien été créé');
                    Tools::redirect($this->context->link->getPageLink('comments'));
                }
            }
        }
        // Si nous sommes déjà connécté, pas besoin de se logger/s'inscrire, redirection vers l'accueil
        elseif(Auth::getUser())
            Tools::redirect($this->context->link->getPageLink('comments'));
    }
}