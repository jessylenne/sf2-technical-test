<?php


class DbPDO extends Db
{
	protected static function _getPDO($host, $user, $password, $dbname, $driver = 'mysql', $timeout = 5)
	{
        switch($driver) {
            case 'sqlite':
                return new PDO('sqlite:'._RESSOURCES_PATH_.Env::get('database.sqlitepath'));
                break;
            case 'mysql':
            default:
                $dsn = 'mysql:';
                if ($dbname)
                    $dsn .= 'dbname='.$dbname.';';
                if (preg_match('/^(.*):([0-9]+)$/', $host, $matches))
                    $dsn .= 'host='.$matches[1].';port='.$matches[2];
                elseif (preg_match('#^.*:(/.*)$#', $host, $matches))
                    $dsn .= 'unix_socket='.$matches[1];
                else
                    $dsn .= 'host='.$host;

                return new PDO($dsn, $user, $password, array(PDO::ATTR_TIMEOUT => $timeout, PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
                break;
        }

        throw new Exception('Unknown Db driver "'.$driver.'"');
	}
	
	public static function createDatabase($host, $user, $password, $dbname, $driver = 'mysql', $dropit = false)
	{
		try {
			$link = DbPDO::_getPDO($host, $user, $password, false, $driver);
			$success = $link->exec('CREATE DATABASE `'.str_replace('`', '\\`', $dbname).'`');
			if ($dropit && ($link->exec('DROP DATABASE `'.str_replace('`', '\\`', $dbname).'`') !== false))
				return true;
		} catch (PDOException $e) {
			return false;
		}
		return $success;
	}
	
	/**
	 * @see DbCore::connect()
	 */
	public function connect()
	{
		try {
			$this->link = $this->_getPDO($this->server, $this->user, $this->password, $this->database, $this->driver, 5);
		} catch (PDOException $e) {
			die(sprintf(Tools::displayError('Link to database cannot be established: %s'), utf8_encode($e->getMessage())));
		}

		// UTF-8 support
		if ($this->driver == 'mysql' && $this->link->exec('SET NAMES \'utf8\'') === false)
			die(Tools::displayError('Fatal error: no utf-8 support. Please check your server configuration.'));

		return $this->link;
	}

	/**
	 * @see DbCore::disconnect()
	 */
	public function disconnect()
	{
		unset($this->link);
	}

	/**
	 * @see DbCore::_query()
	 */
	protected function _query($sql)
	{
		return $this->link->query($sql);
	}

	/**
	 * @see DbCore::nextRow()
	 */
	public function nextRow($result = false)
	{
		if (!$result)
			$result = $this->result;
		return $result->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * @see DbCore::_numRows()
	 */
	protected function _numRows($result)
	{
		return $result->rowCount();
	}

	/**
	 * @see DbCore::Insert_ID()
	 */
	public function Insert_ID()
	{
		return $this->link->lastInsertId();
	}

	/**
	 * @see DbCore::Affected_Rows()
	 */
	public function Affected_Rows()
	{
		return $this->result->rowCount();
	}

	/**
	 * @see DbCore::getMsgError()
	 */
	public function getMsgError($query = false)
	{
		$error = $this->link->errorInfo();
		return ($error[0] == '00000') ? '' : $error[2];
	}

	/**
	 * @see DbCore::getNumberError()
	 */
	public function getNumberError()
	{
		$error = $this->link->errorInfo();
		return isset($error[1]) ? $error[1] : 0;
	}

	/**
	 * @see DbCore::getVersion()
	 */
	public function getVersion()
	{
		return $this->getValue('SELECT VERSION()');
	}

	/**
	 * @see DbCore::_escape()
	 */
	public function _escape($str)
	{
        if(Env::get('database.driver') == 'sqlite') {
            return SQLite3::escapeString($str);
        }

		$search = array("\\", "\0", "\n", "\r", "\x1a", "'", '"');
		$replace = array("\\\\", "\\0", "\\n", "\\r", "\Z", "\'", '\"');		
		return str_replace($search, $replace, $str);
	}

	/**
	 * @see DbCore::set_db()
	 */
	public function set_db($db_name)
	{
		return $this->link->exec('USE '.pSQL($db_name));
	}

	/**
	 * @see Db::hasTableWithSamePrefix()
	 */
	public static function hasTableWithSamePrefix($server, $user, $pwd, $db, $prefix, $driver = 'mysql')
	{
		try {
			$link = DbPDO::_getPDO($server, $user, $pwd, $db, $driver, 5);
		} catch (PDOException $e) {
			return false;
		}

		$sql = 'SHOW TABLES LIKE \''.$prefix.'%\'';
		$result = $link->query($sql);
		return (bool)$result->fetch();
	}

	public static function checkCreatePrivilege($server, $user, $pwd, $db, $driver = 'mysql')
	{
		try {
			$link = DbPDO::_getPDO($server, $user, $pwd, $db, $driver, 5);
		} catch (PDOException $e) {
			return false;
		}

		$sql = '
			CREATE TABLE `test` (
			`test` tinyint(1) unsigned NOT NULL
			) ENGINE=MyISAM';
		$result = $link->query($sql);
		if (!$result)
		{
			$error = $link->errorInfo();
			return $error[2];
		}
		$link->query('DROP TABLE `test`');
		return true;
	}

	/**
	 * @see Db::checkConnection()
	 */
	public static function tryToConnect($server, $user, $pwd, $db, $newDbLink = true, $driver = 'mysql', $timeout = 5)
	{
		try {
			$link = DbPDO::_getPDO($server, $user, $pwd, $db, $driver, $timeout);
		} catch (PDOException $e) {
			return ($e->getCode() == 1049) ? 2 : 1;
		}
		unset($link);
		return 0;
	}
	
	public function getBestEngine()
	{
		$value = 'InnoDB';
		
		$sql = 'SHOW VARIABLES WHERE Variable_name = \'have_innodb\'';
		$result = $this->link->query($sql);
		if (!$result)
			$value = 'MyISAM';
		$row = $result->fetch();
		if (!$row || strtolower($row['Value']) != 'yes')
			$value = 'MyISAM';
		
		/* MySQL >= 5.6 */
		$sql = 'SHOW ENGINES';
		$result = $this->link->query($sql);
		while ($row = $result->fetch())
			if ($row['Engine'] == 'InnoDB')
			{
				if (in_array($row['Support'], array('DEFAULT', 'YES')))
					$value = 'InnoDB';
				break;
			}
		return $value;
	}

	/**
	 * @see Db::checkEncoding()
	 */
	public static function tryUTF8($server, $user, $pwd, $driver = 'mysql')
	{
		try {
			$link = DbPDO::_getPDO($server, $user, $pwd, false, $driver, 5);
		} catch (PDOException $e) {
			return false;
		}
		$result = $link->exec('SET NAMES \'utf8\'');
		unset($link);

		return ($result === false) ? false : true;
	}
}
