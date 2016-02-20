<?php

/**
 * Sanitize data which will be injected into SQL query
 *
 * @param string $string SQL data which will be injected into SQL query
 * @param boolean $htmlOK Does data contain HTML code ? (optional)
 * @return string Sanitized data
 */
function pSQL($string, $htmlOK = false)
{
    // Avoid thousands of "Db::getInstance()"...
    static $db = false;
    if (!$db)
        $db = Db::getInstance();

    return $db->escape($string, $htmlOK);
}