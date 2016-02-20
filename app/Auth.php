<?php

class Auth {
    private static $_user = null;

    /**
     * Retrieve current logged in user
     * @return bool|null|User
     */
    public static function getUser()
    {
        if(self::$_user == null) {
            $id_user = Context::getContext()->cookie->id_user;

            if(!(int)$id_user)
                return self::$_user = false;

            $user = new User($id_user);
            if(Validate::isLoadedObject($user)) {
                Context::getContext()->user = $user;
                return self::$_user = $user;
            }
            else
                return self::$_user = false;
        }

        return self::$_user;
    }

    /**
     * @param User $user
     * @return null|User
     */
    public static function setUser(User $user)
    {
        self::$_user = $user;
        if(self::$_user->id)
            Context::getContext()->cookie->id_user = self::$_user->id;

        return self::$_user;
    }

    /**
     * Disconnect the user (remove his id from cookie)
     */
    public static function disconnect()
    {
        self::$_user = false;
        unset(Context::getContext()->cookie->id_user);
    }
}