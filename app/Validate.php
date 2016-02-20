<?php

class Validate {
    /**
     * Check object validity
     *
     * @param object $object Object to validate
     * @return boolean Validity is ok or not
     */
    public static function isLoadedObject($object)
    {
        return is_object($object) && $object->id;
    }

    /**
     * Check for table or identifier validity
     * Mostly used in database for table names and id_table
     *
     * @param string $table Table/identifier to validate
     * @return boolean Validity is ok or not
     */
    public static function isTableOrIdentifier($table)
    {
        return preg_match('/^[a-zA-Z0-9_-]+$/', $table);
    }

    /**
     * Check for password validity
     *
     * @param string $passwd Password to validate
     * @param int $size
     * @return boolean Validity is ok or not
     */
    public static function isPasswd($passwd, $size = 5)
    {
        return (Tools::strlen($passwd) >= $size && Tools::strlen($passwd) < 255);
    }

    /**
     * Check for e-mail validity
     *
     * @param string $email e-mail address to validate
     * @return boolean Validity is ok or not
     */
    public static function isEmail($email)
    {
        return !empty($email) && preg_match(Tools::cleanNonUnicodeSupport('/^[a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]+[.a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]*@[a-z\p{L}0-9]+[._a-z\p{L}0-9-]*\.[a-z\p{L}0-9]+$/ui'), $email);
    }

    /**
     * Price display method validity
     *
     * @param string $data Data to validate
     * @return boolean Validity is ok or not
     */
    public static function isString($data)
    {
        return is_string($data);
    }

    public static function isNonEmptyString($data)
    {
        return self::isString($data) && Tools::strlen($data);
    }

    /**
     * Check for date validity
     *
     * @param string $date Date to validate
     * @return boolean Validity is ok or not
     */
    public static function isDate($date)
    {
        if (!preg_match('/^([0-9]{4})-((?:0?[0-9])|(?:1[0-2]))-((?:0?[0-9])|(?:[1-2][0-9])|(?:3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})?(\.[0-9]{6})?$/', $date, $matches))
            return false;
        return checkdate((int)$matches[2], (int)$matches[3], (int)$matches[1]);
    }



    /**
     * Check for HTML field validity
     *
     * @param string $html HTML field to validate
     * @return boolean Validity is ok or not
     */
    public static function isCleanHtml($html, $allow_iframe = false)
    {
        $events = 'onmousedown|onmousemove|onmmouseup|onmouseover|onmouseout|onload|onunload|onfocus|onblur|onchange';
        $events .= '|onsubmit|ondblclick|onclick|onkeydown|onkeyup|onkeypress|onmouseenter|onmouseleave|onerror|onselect|onreset|onabort|ondragdrop|onresize|onactivate|onafterprint|onmoveend';
        $events .= '|onafterupdate|onbeforeactivate|onbeforecopy|onbeforecut|onbeforedeactivate|onbeforeeditfocus|onbeforepaste|onbeforeprint|onbeforeunload|onbeforeupdate|onmove';
        $events .= '|onbounce|oncellchange|oncontextmenu|oncontrolselect|oncopy|oncut|ondataavailable|ondatasetchanged|ondatasetcomplete|ondeactivate|ondrag|ondragend|ondragenter|onmousewheel';
        $events .= '|ondragleave|ondragover|ondragstart|ondrop|onerrorupdate|onfilterchange|onfinish|onfocusin|onfocusout|onhashchange|onhelp|oninput|onlosecapture|onmessage|onmouseup|onmovestart';
        $events .= '|onoffline|ononline|onpaste|onpropertychange|onreadystatechange|onresizeend|onresizestart|onrowenter|onrowexit|onrowsdelete|onrowsinserted|onscroll|onsearch|onselectionchange';
        $events .= '|onselectstart|onstart|onstop';

        if (preg_match('/<[\s]*script/ims', $html) || preg_match('/('.$events.')[\s]*=/ims', $html) || preg_match('/.*script\:/ims', $html))
            return false;

        if (!$allow_iframe && preg_match('/<[\s]*(i?frame|form|input|embed|object)/ims', $html))
            return false;

        return true;
    }

    /**
     * Check for an integer validity (unsigned)
     *
     * @param integer $value Integer to validate
     * @return boolean Validity is ok or not
     */
    public static function isUnsignedInt($value)
    {
        return (preg_match('#^[0-9]+$#', (string)$value) && $value < 4294967296 && $value >= 0);
    }

    /**
     * Check for username validity
     *
     * @param string $hook Hook name to validate
     * @return boolean Validity is ok or not
     */
    public static function isUserName($hook)
    {
        return preg_match('/^[a-zA-Z0-9_-]+$/', $hook);
    }

    public static function isNonEmptyArray($results)
    {
        return $results && is_array($results) && sizeof($results);
    }
}