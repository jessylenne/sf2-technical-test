<?php

abstract class Db
{
    /**
     * Constants used by insert() method
     */
    const INSERT = 1;
    const INSERT_IGNORE = 2;
    const REPLACE = 3;

    public $id_server;

    /**
     * @var string Server (eg. localhost)
     */
    protected $server;

    /**
     * @var string Database user (eg. root)
     */
    protected $user;

    /**
     * @var string Database password (eg. can be empty !)
     */
    protected $password;

    /**
     * @var string Database name
     */
    protected $database;

    /**
     * @var string Database name
     */
    protected $driver;

    /**
     * @var bool
     */
    protected $is_cache_enabled;

    /**
     * @var mixed Ressource link
     */
    protected $link;

    /**
     * @var mixed SQL cached result
     */
    protected $result;

    /**
     * @var array List of DB instance
     */
    protected static $instance = array();

    /**
     * @var array Object instance for singleton
     */
    protected static $_servers = array();

    /**
     * Store last executed query
     *
     * @var string
     */
    protected $last_query;

    /**
     * Last cached query
     *
     * @var string
     */
    protected $last_cached;

    /**
     * Open a connection
     */
    abstract public function connect();

    /**
     * Close a connection
     */
    abstract public function disconnect();

    /**
     * Execute a query and get result resource
     *
     * @param string $sql
     * @return mixed
     */
    abstract protected function _query($sql);

    /**
     * Get number of rows in a result
     *
     * @param mixed $result
     */
    abstract protected function _numRows($result);

    /**
     * Get the ID generated from the previous INSERT operation
     */
    abstract public function Insert_ID();

    /**
     * Get number of affected rows in previous database operation
     */
    abstract public function Affected_Rows();

    /**
     * Get next row for a query which doesn't return an array
     *
     * @param mixed $result
     */
    abstract public function nextRow($result = false);

    /**
     * Get database version
     *
     * @return string
     */
    abstract public function getVersion();

    /**
     * Protect string against SQL injections
     *
     * @param string $str
     * @return string
     */
    abstract public function _escape($str);

    /**
     * Returns the text of the error message from previous database operation
     */
    abstract public function getMsgError();

    /**
     * Returns the number of the error from previous database operation
     */
    abstract public function getNumberError();

    /* do not remove, useful for some modules */
    abstract public function set_db($db_name);

    abstract public function getBestEngine();

    /**
     * Get Db object instance
     *
     * @param bool $master Decides whether the connection to be returned by the master server or the slave server
     * @return Db instance
     */
    public static function getInstance($master = true)
    {
        static $id = 0;

        // This MUST not be declared with the class members because some defines (like _DB_SERVER_) may not exist yet (the constructor can be called directly with params)
        if (!self::$_servers)
            self::$_servers = array(
                array(
                    'driver' => Env::get('database.driver', 'sql'),
                    'server' => Env::get('database.server', 'localhost'),
                    'user' => Env::get('database.user', 'root'),
                    'password' => Env::get('database.password', ''),
                    'database' => Env::get('database.type', 'gitcommenter')
                )
            );

        $total_servers = count(self::$_servers);
        if(is_int($master))
            $id_server = $master;
        elseif ($master || $total_servers == 1)
            $id_server = 0;
        else
        {
            $id++;
            $id_server = ($total_servers > 2 && ($id % $total_servers) != 0) ? $id : 1;
        }

        if (!isset(self::$instance[$id_server]))
        {
            $class = Db::getClass();
            self::$instance[$id_server] = new $class(
                self::$_servers[$id_server]['server'],
                self::$_servers[$id_server]['user'],
                self::$_servers[$id_server]['password'],
                self::$_servers[$id_server]['database'],
                self::$_servers[$id_server]['driver'],
                true,
                $id_server
            );
        }

        return self::$instance[$id_server];
    }

    /**
     * Get child layer class
     *
     * @return string
     */
    public static function getClass()
    {
        $class = 'MySQL';
        if (PHP_VERSION_ID >= 50200 && extension_loaded('pdo_mysql'))
            $class = 'DbPDO';
        else if (extension_loaded('mysqli'))
            $class = 'DbMySQLi';
        return $class;
    }

    /**
     * Instantiate database connection
     *
     * @param string $server Server address
     * @param string $user User login
     * @param string $password User password
     * @param string $database Database name
     * @param bool $connect If false, don't connect in constructor (since 1.5.0)
     */
    public function __construct($server, $user, $password, $database, $driver = 'mysql', $connect = true, $id_server = 0)
    {
        $this->id_server = $id_server;
        $this->server = $server;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->driver = $driver;
        $this->is_cache_enabled = (defined('_CACHE_ENABLED_')) ? _CACHE_ENABLED_ : false;

        if (!defined('_DEBUG_SQL_'))
            define('_DEBUG_SQL_', false);

        if ($connect)
            $this->connect();
    }

    public function getDatabaseName() {
        return $this->database;
    }

    /**
     * Close connection to database
     */
    public function __destruct()
    {
        if ($this->link)
            $this->disconnect();
    }

    /**
     * @deprecated 1.5.0 use insert() or update() method instead
     */
    public function autoExecute($table, $data, $type, $where = '', $limit = 0, $use_cache = true, $use_null = false)
    {
        $type = strtoupper($type);
        switch ($type)
        {
            case 'INSERT' :
                return $this->insert($table, $data, $use_null, $use_cache, Db::INSERT, false);

            case 'INSERT IGNORE' :
                return $this->insert($table, $data, $use_null, $use_cache, Db::INSERT_IGNORE, false);

            case 'REPLACE' :
                return $this->insert($table, $data, $use_null, $use_cache, Db::REPLACE, false);

            case 'UPDATE' :
                return $this->update($table, $data, $where, $limit, $use_null, $use_cache, false);

            default :
                throw new Exception('Wrong argument (miss type) in Db::autoExecute()');
        }
    }

    /**
     * Filter SQL query within a blacklist
     *
     * @param string $table Table where insert/update data
     * @param string $values Data to insert/update
     * @param string $type INSERT or UPDATE
     * @param string $where WHERE clause, only for UPDATE (optional)
     * @param int $limit LIMIT clause (optional)
     * @return mixed|boolean SQL query result
     */
    public function autoExecuteWithNullValues($table, $values, $type, $where = '', $limit = 0)
    {
        return $this->autoExecute($table, $values, $type, $where, $limit, 0, true);
    }

    /**
     * Execute a query and get result ressource
     *
     * @param string $sql
     * @return mixed
     */
    public function query($sql)
    {
        if ($sql instanceof DbQuery)
            $sql = $sql->build();

        $this->result = $this->_query($sql);
        if (!$this->result && _DEBUG_SQL_)
            $this->displayError($sql);
        return $this->result;
    }

    /**
     * Execute an INSERT query
     *
     * @param string $table Table name without prefix
     * @param array $data Data to insert as associative array. If $data is a list of arrays, multiple insert will be done
     * @param bool $null_values If we want to use NULL values instead of empty quotes
     * @param bool $use_cache
     * @param int $type Must be Db::INSERT or Db::INSERT_IGNORE or Db::REPLACE
     * @param bool $duplicate_key
     * @return bool
     * @throws Exception
     */
    public function insert($table, $data, $null_values = false, $use_cache = true, $type = Db::INSERT, $duplicate_key = false)
    {
        if (!$data && !$null_values)
            return true;

        if ($type == Db::INSERT)
            $insert_keyword = 'INSERT';
        else if ($type == Db::INSERT_IGNORE)
            $insert_keyword = 'INSERT IGNORE';
        else if ($type == Db::REPLACE)
            $insert_keyword = 'REPLACE';
        else
            throw new Exception('Bad keyword, must be Db::INSERT or Db::INSERT_IGNORE or Db::REPLACE');

        // Check if $data is a list of row
        $current = current($data);
        if (!is_array($current) || isset($current['type']))
            $data = array($data);

        $keys = array();
        $values_stringified = array();
        foreach ($data as $row_data)
        {
            $values = array();
            foreach ($row_data as $key => $value)
            {
                if (isset($keys_stringified))
                {
                    // Check if row array mapping are the same
                    if (!in_array("`$key`", $keys))
                        throw new Exception('Keys form $data subarray don\'t match');
                }
                else
                    $keys[] = "`$key`";

                if (!is_array($value))
                    $value = array('type' => 'text', 'value' => $value);
                if ($value['type'] == 'sql')
                    $values[] = $value['value'];
                else {
                    $values[] = $null_values && ($value['value'] === '' || is_null($value['value'])) ? 'NULL' : "'".$value['value']."'";
                }
            }
            $keys_stringified = implode(', ', $keys);
            $values_stringified[] = '('.implode(', ', $values).')';
        }
        
        $duplicate = '';
        if(is_array($duplicate_key) && count($duplicate_key)){
            $duplicate = 'ON DUPLICATE KEY UPDATE ';
            $tmp =array();
            foreach($duplicate_key as $key=>$d){
                $tmp[] = $key." = '".$d."'";
            }
            $duplicate .= implode(', ', $tmp);
        }

        $sql = $insert_keyword.' INTO `'.$table.'` ('.$keys_stringified.') VALUES '.implode(', ', $values_stringified) . ' ' . $duplicate;
//        exit($sql);
        return (bool)$this->q($sql, $use_cache);
    }

    /**
     * @param string $table Table name without prefix
     * @param array $data Data to insert as associative array. If $data is a list of arrays, multiple insert will be done
     * @param string $where WHERE condition
     * @param int $limit
     * @param bool $null_values If we want to use NULL values instead of empty quotes
     * @param bool $use_cache
     * @param bool $add_prefix Add or not _DB_PREFIX_ before table name
     * @return bool
     */
    public function update($table, $data, $where = '', $limit = 0, $null_values = false, $use_cache = true, $add_prefix = true)
    {
        if (!$data)
            return true;

        $sql = 'UPDATE `'.$table.'` SET ';
        foreach ($data as $key => $value)
        {
            if (!is_array($value))
                $value = array('type' => 'text', 'value' => $value);
            if ($value['type'] == 'sql')
                $sql .= "`$key` = {$value['value']},";
            else
                $sql .= ($null_values && ($value['value'] === '' || is_null($value['value']))) ? "`$key` = NULL," : "`$key` = '{$value['value']}',";
        }

        $sql = rtrim($sql, ',');
        if ($where)
            $sql .= ' WHERE '.$where;
        if ($limit)
            $sql .= ' LIMIT '.(int)$limit;
        return (bool)$this->q($sql, $use_cache);
    }

    /**
     * Execute a DELETE query
     *
     * @param string $table Name of the table to delete
     * @param string $where WHERE clause on query
     * @param int $limit Number max of rows to delete
     * @param bool $use_cache Use cache or not
     * @param bool $add_prefix Add or not _DB_PREFIX_ before table name
     * @return bool
     */
    public function delete($table, $where = '', $limit = 0, $use_cache = true, $add_prefix = true)
    {
        $this->result = false;
        $sql = 'DELETE FROM `'.$table.'`'.($where ? ' WHERE '.$where : '').($limit ? ' LIMIT '.(int)$limit : '');
        $res = $this->query($sql);
        if ($use_cache && $this->is_cache_enabled)
            Cache::getInstance()->deleteQuery($sql);
        return (bool)$res;
    }

    /**
     * Execute a query
     *
     * @param string $sql
     * @param bool $use_cache
     * @return bool
     */
    public function execute($sql, $use_cache = true)
    {
        if ($sql instanceof DbQuery)
            $sql = $sql->build();

        $this->result = $this->query($sql);
        if ($use_cache && $this->is_cache_enabled)
            Cache::getInstance()->deleteQuery($sql);
        return (bool)$this->result;
    }

    /**
     * ExecuteS return the result of $sql as array
     *
     * @param string $sql query to execute
     * @param boolean $array return an array instead of a mysql_result object (deprecated since 1.5.0, use query method instead)
     * @param bool $use_cache if query has been already executed, use its result
     * @return array or result object
     */
    public function executeS($sql, $array = true, $use_cache = true)
    {
        if ($sql instanceof DbQuery)
            $sql = $sql->build();

        // This method must be used only with queries which display results
        if (!preg_match('#^\s*\(?\s*(select|show|explain|describe|desc)\s#i', $sql))
        {
            if (defined('_MODE_DEV_') && _MODE_DEV_)
                throw new Exception('Db->executeS() must be used only with select, show, explain or describe queries');
            return $this->execute($sql, $use_cache);
        }

        $this->result = false;
        $this->last_query = $sql;
        if ($use_cache && $this->is_cache_enabled && $array && ($result = Cache::getInstance()->get(md5($sql))))
        {
            $this->last_cached = true;
            return $result;
        }

        $this->result = $this->query($sql);
        if (!$this->result)
            return false;

        $this->last_cached = false;
        if (!$array)
            return $this->result;

        $result_array = array();
        while ($row = $this->nextRow($this->result))
            $result_array[] = $row;

        if ($use_cache && $this->is_cache_enabled)
            Cache::getInstance()->setQuery($sql, $result_array);
        return $result_array;
    }

    /**
     * getRow return an associative array containing the first row of the query
     * This function automatically add "limit 1" to the query
     *
     * @param mixed $sql the select query (without "LIMIT 1")
     * @param bool $use_cache find it in cache first
     * @param bool $limit Début du limit
     * @return array associative array of (field=>value)
     */
    public function getRow($sql, $use_cache = true, $limit = '')
    {
        if ($sql instanceof DbQuery)
            $sql = $sql->build();

        $sql .= ' LIMIT '. ($limit!=''? $limit.', ': '') .' 1';
        $this->result = false;
        $this->last_query = $sql;
        if ($use_cache && $this->is_cache_enabled && ($result = Cache::getInstance()->get(md5($sql))))
        {
            $this->last_cached = true;
            return $result;
        }
        $this->result = $this->query($sql);
        if (!$this->result)
            return false;
        $this->last_cached = false;
        $result = $this->nextRow($this->result);
        if (is_null($result))
            $result = false;
        if ($use_cache && $this->is_cache_enabled)
            Cache::getInstance()->setQuery($sql, $result);
        return $result;
    }

    /**
     * getValue return the first item of a select query.
     *
     * @param mixed $sql
     * @param bool $use_cache
     * @return mixed
     */
    public function getValue($sql, $use_cache = true)
    {
        if ($sql instanceof DbQuery)
            $sql = $sql->build();

        if (!$result = $this->getRow($sql, $use_cache))
            return false;
        return array_shift($result);
    }

    /**
     * Get number of rows for last result
     *
     * @return int
     */
    public function numRows()
    {
        if (!$this->last_cached && $this->result)
        {
            $nrows = $this->_numRows($this->result);
            if ($this->is_cache_enabled)
                Cache::getInstance()->set(md5($this->last_query).'_nrows', $nrows);
            return $nrows;
        }
        else if ($this->is_cache_enabled && $this->last_cached)
            return Cache::getInstance()->get(md5($this->last_query).'_nrows');
    }

    /**
     *
     * Execute a query
     *
     * @param string $sql
     * @param bool $use_cache
     * @return mixed $result
     */
    protected function q($sql, $use_cache = true)
    {
        if ($sql instanceof DbQuery)
            $sql = $sql->build();

        $this->result = false;
        $result = $this->query($sql);
        if ($use_cache && $this->is_cache_enabled)
            Cache::getInstance()->deleteQuery($sql);
        if (_DEBUG_SQL_)
            $this->displayError($sql);
        return $result;
    }

    /**
     * Display last SQL error
     *
     * @param bool $sql
     * @throws Exception
     */
    public function displayError($sql = false)
    {
        //var_export(debug_backtrace());
        //exit($this->getMsgError().' '.$sql);
        if(_MODE_DEV_)
        {
            if ($sql)
                throw new Exception($this->getMsgError().'<br /><br /><pre>'.$sql.'</pre>');

            throw new Exception($this->getMsgError());
        }
        else
        {
            if ($sql)
                Tools::displayError($this->getMsgError().'<br /><br /><pre>'.$sql.'</pre>');

            Tools::displayError($this->getMsgError());
        }
    }

    /**
     * Sanitize data which will be injected into SQL query
     *
     * @param string $string SQL data which will be injected into SQL query
     * @param boolean $html_ok Does data contain HTML code ? (optional)
     * @return string Sanitized data
     */
    public function escape($string, $html_ok = false)
    {
        if (defined('_MAGIC_QUOTES_GPC_') && _MAGIC_QUOTES_GPC_)
            $string = stripslashes($string);
        if (!is_numeric($string))
        {
            $string = $this->_escape($string);
            if (!$html_ok)
                $string = Tools::nl2br(strip_tags($string));
        }

        return $string;
    }

    /**
     * Try a connection to te database
     *
     * @param string $server Server address
     * @param string $user Login for database connection
     * @param string $pwd Password for database connection
     * @param string $db Database name
     * @param bool $new_db_link
     * @param bool $engine
     * @return int
     */
    public static function checkConnection($server, $user, $pwd, $db, $new_db_link = true, $engine = null, $timeout = 5)
    {
        return call_user_func_array(array(Db::getClass(), 'tryToConnect'), array($server, $user, $pwd, $db, $new_db_link, $engine, $timeout));
    }

    /**
     * Try a connection to te database
     *
     * @param string $server Server address
     * @param string $user Login for database connection
     * @param string $pwd Password for database connection
     * @return int
     */
    public static function checkEncoding($server, $user, $pwd)
    {
        return call_user_func_array(array(Db::getClass(), 'tryUTF8'), array($server, $user, $pwd));
    }

    /**
     * Try a connection to the database and check if at least one table with same prefix exists
     *
     * @param string $server Server address
     * @param string $user Login for database connection
     * @param string $pwd Password for database connection
     * @param string $db Database name
     * @param string $prefix Tables prefix
     * @return bool
     */
    public static function hasTableWithSamePrefix($server, $user, $pwd, $db, $prefix)
    {
        return call_user_func_array(array(Db::getClass(), 'hasTableWithSamePrefix'), array($server, $user, $pwd, $db, $prefix));
    }

    public static function checkCreatePrivilege($server, $user, $pwd, $db, $driver = 'mysql')
    {
        return call_user_func_array(array(Db::getClass(), 'checkCreatePrivilege'), array($server, $user, $pwd, $db, $driver));
    }

    /**
     * @deprecated 1.5.0
     */
    public static function s($sql, $use_cache = true)
    {
        return Db::getInstance()->executeS($sql, true, $use_cache);
    }

    /**
     * @deprecated 1.5.0
     */
    public static function ps($sql, $use_cache = 1)
    {
        $ret = Db::s($sql, $use_cache);
        p($ret);
        return $ret;
    }

    /**
     * @deprecated 1.5.0
     */
    public static function ds($sql, $use_cache = 1)
    {
        Db::s($sql, $use_cache);
        die();
    }
}
