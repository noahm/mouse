<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Database Module
 *
 * @author		Alexia E. Smith
 * @license		GNU General Public License v3
 * @package		Mouse Framework
 * @link		https://github.com/Alexia/mouse
 *
**/
namespace mouse\database;
use mouse;

abstract class module {
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
	 * Default Host
	 *
	 * @var		string
	 */
	protected $defaultHost = '127.0.0.1';

	/**
	 * Default Port
	 *
	 * @var		integer
	 */
	protected $defaultPort = 3306;

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	string	[Optional] Object key used to initialize the object to mouse.  Also serves as the settings array key.
	 * @return	void
	 */
	public function __construct($objectKey = 'DB') {
		$this->objectKey	= $objectKey;
		$this->settings		=& mouse\hole::$settings[$this->objectKey];

		if ($this->settings['prefix'] === null) {
			$this->settings['prefix'] = '';
		}

		if (in_array($this->settings['result_type'], ['array', 'object'])) {
			$this->setResultType($this->settings['result_type']);
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
			$this->disconnect();
		}
		if (is_object($this->mysqli)) {
			unset($this->mysqli);
		}
		$this->mysqli = new mysqli();
		$this->connect(
			($this->settings['server'] ? $this->settings['server'] : $this->defaultHost),
			$this->settings['user'],
			$this->settings['pass'],
			$this->settings['database'],
			($this->settings['port'] ? $this->settings['port'] : $this->defaultPort)
		);
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
	 * @return	boolean	Success
	 */
	abstract public function connect($server, $user, $pass, $db, $port);

	/**
	 * Disconnect from Database
	 *
	 * @access	public
	 * @return	void
	 */
	abstract public function disconnect();

	/**
	 * Select Database
	 *
	 * @access	public
	 * @param	string	Database name
	 * @return	void
	 */
	abstract public function selectDatabase($database);

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
	abstract public function getAffectedRows();

	/**
	 * Returns the insert ID of the last insert.
	 *
	 * @access	public
	 * @return	integer
	 */
	abstract public function getInsertID();

	/**
	 * Returns the number of rows from a given select statement result or the most recent select statement
	 *
	 * @access	public
	 * @param	object	[optional] Query Object
	 * @return	integer
	 */
	abstract public function getNumRows($result = null);

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
	abstract public function truncate($table);

	/**
	 * Begin a Transaction
	 *
	 * @access	public
	 * @return	boolean
	 */
	abstract public function transactionStart();

	/**
	 * Commit and finalize a transaction
	 *
	 * @access	public
	 * @return	boolean	True on success, false on failure.
	 */
	abstract public function transactionCommit();

	/**
	 * Rollback a transaction
	 *
	 * @access	public
	 * @return	boolean	True on success, false on failure.
	 */
	abstract public function transactionRollback();

	/**
	 * Escape value to use in a query
	 *
	 * @access	public
	 * @param	string	Value to escape
	 * @return	string	Escaped value
	 */
	abstract public function escapeString($value);

	/**
	 * Database Direct Query
	 *
	 * @access	public
	 * @param	string	Precompiled query
	 * @return	mixed
	 */
	abstract public function query($query);

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
	 * Set resultType to specified type.
	 *
	 * @access	public
	 * @return	void
	 */
	public function setResultType($resultType) {
		$this->resultType = $resultType;
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
	 * Is this database connection thread safe?
	 *
	 * @access	public
	 * @return	boolean
	 */
	abstract public function isThreadSafe();
}
?>