<?php

class User extends ObjectModel {
    public $login;
    public $password;
    public $date_add;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'user',
        'primary' => 'id_user',
        'fields' => array(
            'login' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 32),
            'password' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 32),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    /**
     * Récupération de l'employé par identifiant (et mot de passe facultatif)
     *
     * @param $email
     * @param string $passwd Password is also checked if specified
     * @return User instance
     */
    public function getByEmail($email, $passwd = null) {
        if (!Validate::isEmail($email) || ($passwd != null && !Validate::isPasswd($passwd)))
            die(Tools::displayError());

        $passwd = trim($passwd);

        $query = DbQuery::get()
            ->select('*')
            ->from('user')
            ->where('login = "'.pSQL($email).'"');

        if($passwd)
            $query->where('password = "'.Tools::encrypt($passwd).'"');

        $result = Db::getInstance()->getRow($query);

        if (!$result)
            return false;

        $this->id = $result['id_user'];

        foreach ($result as $key => $value)
            if (property_exists($this, $key))
                $this->{$key} = $value;

        return $this;
    }
}