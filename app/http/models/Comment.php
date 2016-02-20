<?php

class Comment extends ObjectModel {
    public $id_user;
    public $username;
    public $repository;
    public $comment;
    public $date_add;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'comment',
        'primary' => 'id_comment',
        'fields' => array(
            'id_user' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true, 'size' => 11),
            'username' => array('type' => self::TYPE_STRING, 'validate' => 'isUserName', 'required' => true, 'size' => 32),
            'repository' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 32),
            'comment' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHTML', 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public function user()
    {
        return new User($this->id_user);
    }

    public function getDate()
    {
        return $this->date_add;
    }

    /**
     * @param User $user
     * @return Comment
     */
    public function setUser(User $user)
    {
        $this->id_user = $user->id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param $repository
     * @return $this
     * @todo check repository <-> username Github's relationship
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param $comment
     * @return $this
     * @throws Exception
     */
    public function setComment($comment)
    {
        if(!Validate::isCleanHTML($comment))
            throw new Exception('Veuillez fournir un commentaire valide');

        $this->comment = $comment;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }
}