<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Database MySQL - Interface to MySQL Data, provides automatic connection setup, and object oriented database calls.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2014 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/

class mouseDatabaseMysql {
	/**
	 * MySQL Link
	 *
	 * @var		object
	 */
	private $mysql;

	/**
	 * Object Key
	 *
	 * @var		object
	 */
	public $objectKey;

	/**
	 * DEPRECATED
	 * Constructor
	 *
	 * @access	public
	 * @param	[Optional] Object key used to initialize the object to mouse.  Also serves as the settings array key.
	 * @return	void
	 */
	public function __construct($objectKey = 'DB') {
		$this->objectKey	= $objectKey;
		$this->settings		=& mouseHole::$settings[$this->objectKey];

		//Automatic enable.
		if ($this->settings['use_database']) {
			$this->enabled	= $this->init();
		} else {
			$this->enabled	= false;
		}
	}

	/**
	 * Automatically initiate database connection and select a database.
	 *
	 * @access	public
	 * @return	void
	 */
	public function init() {
		if (intval($this->settings['port']) > 0) {
			$this->settings['server'] = $this->settings['server'].':'.intval($this->settings['port']);
		}
		$this->connect($this->settings['server'], $this->settings['user'], $this->settings['pass']);
		$this->selectDatabase($this->settings['database']);
		$this->query("SET NAMES utf8");
		return true;
	}

	/**
	 * Connect to Database
	 *
	 * @access	public
	 * @param	string	Server address
	 * @param	string	Username
	 * @param	string	Password
	 * @return	void
	 */
	public function connect($server, $user, $pass) {
		$this->mysql = @mysql_connect($server, $user, $pass, true);
		if (!$this->mysql) {
			$this->dbError();
		}
	}

	/**
	 * Select Database
	 *
	 * @access	public
	 * @param	string	Database name
	 * @return	void
	 */
	public function selectDatabase($database) {
		if (!mysql_select_db($database, $this->mysql)) {
			$this->dbError();
		}
	}

	/**
	 * Database Select
	 *
	 * @access	public
	 * @param	array		Array of data to build the select statement.
	 * @return	resource	Query resource
	 */
	public function select($data = array()) {
		$where = array();
		$from = array();
		$left = array();

		if (array_key_exists('add_join', $data) && is_array($data['add_join'])) {
			foreach ($data['add_join'] as $key => $join) {
				($join['select'] ? $select[] = $join['select'] : null);
				($join['where'] ? $where[] = $join['where'] : null);
				switch ($join['type']) {
					default:
					case 'inner':
						$from[] = $this->buildFrom($join['from']);
						break;
					case 'left':
						$left[] = 'LEFT JOIN '.$this->buildFrom($join['from']).' ON '.$join['on'];
						break;
				}
			}
		}

		($data['select'] ? $select[] = $data['select'] : null);
		$from[] = $this->buildFrom($data['from']);
		($data['where'] ? $where[] = $data['where'] : null);

		$query = 'SELECT '.implode(', ', $select).' FROM '.implode(', ', $from).(count($left) ? ' '.implode(' ', $left) : '');

		if (count($where)) {
			$query .= ' WHERE '.implode(' AND ', $where);
		}

		if (array_key_exists('group', $data)) {
			$query .= ' GROUP BY '.$data['group'];
		}

		if (array_key_exists('having', $data)) {
			$query .= ' HAVING '.$data['having'];
		}

		if (array_key_exists('order', $data)) {
			$query .= ' ORDER BY '.$data['order'];
		}

		if (array_key_exists('limit', $data) && is_array($data['limit'])) {
			if (count($data['limit']) == 2) {
				$query .= ' LIMIT '.$data['limit'][0].','.$data['limit'][1];
			} elseif (count($data['limit']) == 1) {
				$query .= ' LIMIT '.$data['limit'][0];
			}
		}

		if (array_key_exists('for_update', $data) && $data['for_update'] === true) {
			$query .= ' FOR UPDATE';
		}

		if (array_key_exists('lock_share_mode', $data) && $data['lock_share_mode'] === true) {
			$query .= ' LOCK IN SHARE MODE';
		}

		$this->generatedQuery = $query;
		$result = $this->query($this->generatedQuery);
		if (!$result) {
			$this->dbError();
		}
		return $result;
	}

	/**
	 * Wrapper for the Select and Fetch functions to get the first result.
	 *
	 * @access	public
	 * @param	array	Array of data to build the select statement.
	 * @return	boolean
	 */
	public function selectAndFetch($data = array()) {
		$this->select($data);
		$result = $this->fetch();
		unset($this->queryResult);
		return $result;
	}

	/**
	 * Returns the insert ID of the last insert.
	 *
	 * @access	public
	 * @return	integer
	 */
	public function getInsertID() {
		return mysql_insert_id($this->mysql);
	}

	/**
	 * Returns the number of rows from a select statement.
	 *
	 * @access	public
	 * @param	resource	Query Resource
	 * @return	integer
	 */
	public function getNumRows($result) {
		return mysql_num_rows($result);
	}

	/**
	 * Database Insert
	 *
	 * @access	public
	 * @param	string	Table name
	 * @param	array	Array of field to value data to be inserted.
	 * @return	boolean
	 */
	public function insert($table, $data = array()) {
		$table = $this->settings['prefix'].$table;

		if (!is_array($data) || !count($data)) {
			return false;
		}

		foreach ($data as $field => $value) {
			if ($value === null) {
				$fields[] = $field;
				$values[] = "NULL";
			} else {
				$fields[] = $field;
				$values[] = "'".$this->escapeString($value)."'";
			}
		}

		$this->generatedQuery = 'INSERT INTO '.$table.' (`'.implode('`, `', $fields).'`) VALUES ('.implode(', ', $values).')';

		$result = $this->query($this->generatedQuery);
		if (!$result) {
			$this->dbError();
		}
		return $result;
	}

	/**
	 * Database Update
	 *
	 * @access	public
	 * @param	string	Table name
	 * @param	array	Array of field to value data to be updated
	 * @param	string	Where delimiter clause
	 * @return	boolean
	 */
	public function update($table, $data = array(), $where = false) {
		$table = $this->settings['prefix'].$table;

		foreach ($data as $field => $value) {
			if ($value === null) {
				$set[] = '`'.$field."` = NULL";
			} else {
				$set[] = '`'.$field."` = '".$this->escapeString($value)."'";
			}
		}

		$query = 'UPDATE '.$table.' SET '.implode(', ', $set);

		if ($where) {
			$query .= ' WHERE '.$where;
		}

		$this->generatedQuery = $query;
		$result = $this->query($this->generatedQuery);
		if (!$result) {
			$this->dbError();
		}
		return $result;
	}

	/**
	 * Database Delete
	 *
	 * @access	public
	 * @param	string	Table name
	 * @param	string	Where delimiter clause
	 * @return	boolean
	 */
	public function delete($table, $where = false) {
		$table = $this->settings['prefix'].$table;

		$query = 'DELETE FROM '.$table;

		if ($where) {
			$query .= ' WHERE '.$where;
		} else {
			return false;
		}

		$this->generatedQuery = $query;
		$result = $this->query($this->generatedQuery);
		if (!$result) {
			$this->dbError();
		}
		return $result;
	}

	/**
	 * Database Truncate
	 *
	 * @access	public
	 * @param	string	Table name
	 * @return	boolean
	 */
	public function truncate($table) {
		$table = $this->settings['prefix'].$table;

		$query = 'TRUNCATE TABLE '.$table;

		$this->generatedQuery = $query;
		$result = $this->query($this->generatedQuery);

		return $result;
	}

	/**
	 * Escape value to use in a query
	 *
	 * @access	public
	 * @param	string	Value to escape
	 * @return	string	Escaped value
	 */
	public function escapeString($value) {
		return mysql_real_escape_string($value, $this->mysql);
	}

	/**
	 * Database Direct Query
	 *
	 * @access	public
	 * @param	string	Precompiled query
	 * @return	mixed
	 */
	public function query($query) {
		$result = mysql_query($query, $this->mysql);
		if (is_resource($result)) {
			$this->queryResult = $result;
		}
		return $result;
	}

	/**
	 * Database Fetch Array
	 *
	 * @access	public
	 * @param	resource	[Optional]Query resource
	 * @return	mixed
	 */
	public function fetch($query = null) {
		if (!$query && !$this->queryResult) {
			return false;
		} elseif (!$query && $this->queryResult) {
			$query = $this->queryResult;
		}
		return mysql_fetch_array($query);
	}

	public function dbError() {
		throw new Exception(mysql_error()."\n".$this->generatedQuery."\n");
	}

	/**
	 * Determine how to syntax a from clause
	 *
	 * @access	private
	 * @param	mixed	String or array of from clause.
	 * @return	mixed
	 */
	private function buildFrom($from) {
		if (is_array($from)) {
			foreach ($from as $table => $alias) {
				$froms[] = $this->settings['prefix'].$table.' AS '.$alias;
			}
			return implode(', ', $froms);
		} else {
			return $this->settings['prefix'].$from;
		}
	}
}
?>