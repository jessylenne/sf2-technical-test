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
}