<?php

/**
 * Class Flash
 * Permet de stocker des messages d'information le temps d'une redirection, ...
**/

class Flash {
    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'error';
    const TYPE_INFO = 'info';

    /**
     * Add a success message to be retrieved later
     * @param string $msg
     */
    public static function success($msg)
    {
        self::add($msg, self::TYPE_SUCCESS);
    }

    /**
     * Add an error message to be retrieved later
     * @param string $msg
     */
    public static function error($msg)
    {
        self::add($msg, self::TYPE_ERROR);
    }

    /**
     * Add a message to the stored ones
     * @param string $msg
     * @param string $type
     * @return string
     */
    public static function add($msg, $type = Flash::TYPE_SUCCESS) {
        $messages = self::getMessages();

        if(!isset($messages[$type]))
            $messages[$type] = array();

        $messages[$type][] = $msg;

        self::$_messages = $messages;

        return self::writeMessages();
    }

    private static $_messages;

    /**
     * Retrieve currently stored messages
     * @param null $type
     * @param bool $andFlush And clean them up!
     * @return array|mixed
     */
    public static function getMessages($type = null, $andFlush = false) {
        $messages = Context::getContext()->cookie->flash;
        $messages =  self::$_messages = json_decode($messages, true);

        if($type) {
            if(!isset($messages[$type]))
                return array();

            if($andFlush) {
                self::flush($type);
            }
            return $messages[$type];
        }

        if($andFlush)
            self::flush();

        return $messages ? $messages : array();
    }

    /**
     * @param null $type
     */
    public static function flush($type = null)
    {
        if($type)
            unset(self::$_messages[$type]);
        else
            self::$_messages = '';

        self::writeMessages();
    }

    /**
     * Store messages in cache (cookie/session/...)
     * @return string
     */
    public static function writeMessages() {
        if(sizeof(self::$_messages)) {
            $messages = json_encode(self::$_messages);
            Context::getContext()->cookie->flash = $messages;
        }
        else {
            Context::getContext()->cookie->flash = '';
            unset(Context::getContext()->cookie->flash);
        }
    }
}