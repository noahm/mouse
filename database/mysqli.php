<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Database MySQLi - Interface to MySQLi Data, provides automatic connection setup, and object oriented database calls.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2014 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/
namespace mouse\Database;
use mouse;

class Mysqli {
	/**
	 * MySQLi Instance
	 *
	 * @var		object
	 */
	private $mysqli;

	/**
	 * Object Key
	 *
	 * @var		object
	 */
	public $objectKey;

	/**
	 * Result type to use, array or object.
	 *
	 * @var		string
	 */
	private $resultType = 'array';

	/**
	 * Currently Connected
	 *
	 * @var		boolean
	 */
	private $connected = false;

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	[Optional] Object key used to initialize the object to mouse.  Also serves as the settings array key.
	 * @return	void
	 */
	public function __construct($objectKey = 'DB') {
		$this->objectKey	= $objectKey;
		$this->settings		=& mouse\Hole::$settings[$this->objectKey];

		if ($this->settings['prefix'] === null) {
			$this->settings['prefix'] = '';
		}

		if (in_array($this->settings['result_type'], ['array', 'object'])) {
			$this->resultType = $this->settings['result_type'];
		}

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
		if ($this->connected) {
			$this->mysqli->close();
		}
		if (is_object($this->mysqli)) {
			unset($this->mysqli);
		}
		$this->mysqli = new mysqli();
		$this->connect($this->settings['server'], $this->settings['user'], $this->settings['pass'], $this->settings['database'], ($this->settings['port'] ? $this->settings['port'] : 3306));
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
	 * @param	string	Database name
	 * @param	integer	Server port
	 * @return	void
	 */
	public function connect($server, $user, $pass, $db, $port) {
		$success = @$this->mysqli->real_connect(($this->settings['persistent'] ? 'p:' : null).$server, $user, $pass, $db, $port);
		if (!$success || $this->mysqli->connect_error) {
			$this->dbError();
		} else {
			$this->connected = true;
		}
	}

	/**
	 * Disconnect from Database
	 *
	 * @access	public
	 * @return	void
	 */
	public function disconnect() {
		$this->mysqli->close();
		$this->connected = false;
	}

	/**
	 * Select Database
	 *
	 * @access	public
	 * @param	string	Database name
	 * @return	void
	 */
	public function selectDatabase($database) {
		if (!$this->mysqli->select_db($database)) {
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
	public function select($data = []) {
		$where = [];
		$from = [];
		$left = [];

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

		return $result;
	}

	/**
	 * Wrapper for the Select and Fetch functions to get the first result.
	 *
	 * @access	public
	 * @param	array	Array of data to build the select statement.
	 * @param	string	[Optional] Data type to return, array or object.
	 * @return	boolean
	 */
	public function selectAndFetch($data, $resultType = null) {
		$result = $this->select($data);

		$result = $this->fetch($result, $resultType);

		return $result;
	}

	/**
	 * Returns the number of affected rows for the last INSERT, UPDATE, REPLACE or DELETE query.
	 *
	 * @access	public
	 * @return	integer
	 */
	public function getAffectedRows() {
		return $this->mysqli->affected_rows;
	}

	/**
	 * Returns the insert ID of the last insert.
	 *
	 * @access	public
	 * @return	integer
	 */
	public function getInsertID() {
		return $this->mysqli->insert_id;
	}

	/**
	 * Returns the number of rows from a given select statement result or the most recent select statement
	 *
	 * @access	public
	 * @param	object	[optional] Query Object
	 * @return	integer
	 */
	public function getNumRows($result = null) {
		if ($result == null) $result = $this->queryResult;
		return $result->num_rows;
	}

	/**
	 * Database Insert
	 *
	 * @access	public
	 * @param	string	Table name
	 * @param	array	Array of field to value data to be inserted.
	 * @return	boolean
	 */
	public function insert($table, $data = []) {
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
	public function update($table, $data = [], $where = false) {
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
	 * Begin a Transaction
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function transactionStart() {
		$result = $this->query('START TRANSACTION');

		return $result;
	}

	/**
	 * Commit and finalize a transaction
	 *
	 * @access	public
	 * @return	boolean	True on success, false on failure.
	 */
	public function transactionCommit() {
		return $this->mysqli->commit();
	}

	/**
	 * Rollback a transaction
	 *
	 * @access	public
	 * @return	boolean	True on success, false on failure.
	 */
	public function transactionRollback() {
		return $this->mysqli->rollback();
	}

	/**
	 * Escape value to use in a query
	 *
	 * @access	public
	 * @param	string	Value to escape
	 * @return	string	Escaped value
	 */
	public function escapeString($value) {
		return $this->mysqli->real_escape_string($value);
	}

	/**
	 * Database Direct Query
	 *
	 * @access	public
	 * @param	string	Precompiled query
	 * @return	mixed
	 */
	public function query($query) {
		$result = $this->mysqli->query($query);
		if ($result instanceof mysqli_result) {
			$this->queryResult = $result;
		}

		if (!$result) {
			$this->dbError();
		}

		return $result;
	}

	/**
	 * Database Fetch Array
	 *
	 * @access	public
	 * @param	object	[Optional] MySQLi Result Object
	 * @param	string	[Optional] One time override of data type to return, array or object.
	 * @return	mixed
	 */
	public function fetch($query = null, $resultType = null) {
		if (!$query instanceof mysqli_result && !$this->queryResult instanceof mysqli_result) {
			return false;
		} elseif (!$query instanceof mysqli_result && $this->queryResult instanceof mysqli_result) {
			$query = $this->queryResult;
		}

		if (in_array($resultType, ['array', 'object'])) {
			$_resultType = $resultType;
		} else {
			$_resultType = $this->resultType;
		}

		if ($_resultType == 'object') {
			$result = $query->fetch_object();
		} else {
			$result = $query->fetch_assoc();
		}

		return $result;
	}

	/**
	 * Database Error Handler
	 *
	 * @access	public
	 * @return	void	Throws Exception
	 */
	public function dbError() {
		$errno = ($this->mysqli->connect_errno ? $this->mysqli->connect_errno : $this->mysqli->errno);
		$error = ($this->mysqli->connect_error ? $this->mysqli->connect_error : $this->mysqli->error);
		throw new \Exception('['.$errno.'] '.$error."\n".(PHP_SAPI == 'cli' ? $this->generatedQuery."\n" : ''));
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

	/**
	 * Set resultType to arrays.
	 *
	 * @access	public
	 * @return	void
	 */
	public function setResultTypeArray() {
		$this->resultType = 'array';
	}

	/**
	 * Set resultType to objects.
	 *
	 * @access	public
	 * @return	void
	 */
	public function setResultTypeObject() {
		$this->resultType = 'object';
	}

	/**
	 * Return the current resultType.
	 *
	 * @access	public
	 * @return	string	Current result type.
	 */
	public function getResultType() {
		return $this->resultType;
	}

	/**
	 * Is this MySQLi thread safe?
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function isThreadSafe() {
		return $this->mysqli->thread_safe();
	}
}
?>