<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Database MySQL - Interface to MySQL Data, provides automatic connection setup, and object oriented database calls.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2012 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/

class mouseDatabaseMysql {
	private $db_link;

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct($mouse) {
		$this->config	=& mouseHole::$config;

		//Automatic enable.
		if ($this->config['use_database']) {
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
		if (intval($this->config['db']['port']) > 0) {
			$this->config['db']['server'] = $this->config['db']['server'].':'.intval($this->config['db']['port']);
		}
		$this->connect($this->config['db']['server'], $this->config['db']['user'], $this->config['db']['pass']);
		$this->selectDatabase($this->config['db']['database']);
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
		$this->db_link = @mysql_connect($server, $user, $pass, true);
		if (!$this->db_link) {
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
		if (!mysql_select_db($database, $this->db_link)) {
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
		if (is_array($data['add_join'])) {
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
		
		if ($where) {
			$query .= ' WHERE '.implode(' AND ', $where);
		}

		if ($data['group']) {
			$query .= ' GROUP BY '.$data['group'];
		}

		if ($data['order']) {
			$query .= ' ORDER BY '.$data['order'];
		}

		if ($data['limit']) {
			if (count($data['limit']) == 2) {
				$query .= ' LIMIT '.$data['limit'][0].','.$data['limit'][1];
			} elseif (count($data['limit']) == 1) {
				$query .= ' LIMIT '.$data['limit'][0];
			}
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
		return mysql_insert_id($this->db_link);
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
		$table = $this->config['db']['prefix'].$table;
		
		foreach ($data as $field => $value) {
			if (is_numeric($value) and !is_infinite($value)) {
				$fields[] = $field;
				$values[] = floatval($value);
			} else {
				$fields[] = $field;
				$values[] = "'".$this->escapeString($value)."'";
			}
		}
		
		$this->generatedQuery = 'INSERT INTO '.$table.' ('.implode(', ', $fields).') VALUES ('.implode(', ', $values).')';

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
		$table = $this->config['db']['prefix'].$table;
		
		foreach ($data as $field => $value) {
			if (is_numeric($value) and !is_infinite($value)) {
				$set[] = $field.' = '.floatval($value);
			} else {
				$set[] = $field." = '".$this->escapeString($value)."'";
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
		$table = $this->config['db']['prefix'].$table;

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
	 * Escape value to use in a query
	 *
	 * @access	public
	 * @param	string	Value to escape
	 * @return	string	Escaped value
	 */
	public function escapeString($value) {
		return mysql_real_escape_string($value, $this->db_link);
	}

	/**
	 * Database Direct Query
	 *
	 * @access	public
	 * @param	string	Precompiled query
	 * @return	mixed
	 */
	public function query($query) {
		$result = mysql_query($query, $this->db_link);
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
		if (!$query and !$this->queryResult) {
			return false;
		} elseif (!$query and $this->queryResult) {
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
				$froms[] = $this->config['db']['prefix'].$table.' AS '.$alias;
			}
			return implode(', ', $froms);
		} else {
			return $this->config['db']['prefix'].$from;
		}
	}
}
?>