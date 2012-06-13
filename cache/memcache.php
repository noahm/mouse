<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Cache Memcache - Interface to Memcache, provides automatic connection setup.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2012 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/

class mouseCacheMemcache extends Memcache {
	private $memcache_link;

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct($mouse) {
		$this->config	=& mouseHole::$config;

		//Automatic enable.
		if ($this->config['use_memcache']) {
			$this->enabled	= $this->init();
		} else {
			$this->enabled	= false;
		}
	}

	/**
	 * Automatically initiate memcache connection.
	 *
	 * @access	public
	 * @return	void
	 */
	public function init() {
		return @$this->connect($this->config['memcache']['server'], $this->config['memcache']['port']);
	}

	/**
	 * Memcache Add with automatic prefixing.
	 *
	 * @access	public
	 * @param	string	Key for stored item
	 * @param	mixed	Item to store
	 * @param	mixed	Memcache flags
	 * @param	integer	Seconds until expiration
	 * @return	mixed
	 */
	public function add($key, $var, $flags, $expire) {
		return Memcache::add($this->config['memcache']['prefix'].$key, $var, $flags, $expire);
	}

	/**
	 * Memcache Delete with automatic prefixing.
	 *
	 * @access	public
	 * @param	string	Key for stored item
	 * @return	mixed
	 */
	public function delete($key) {
		return Memcache::delete($this->config['memcache']['prefix'].$key);
	}

	/**
	 * Memcache Get with automatic prefixing.
	 *
	 * @access	public
	 * @param	string	Key for stored item
	 * @param	mixed	[Optional] Memcache flags
	 * @return	mixed
	 */
	public function get($key, $flags = null) {
		if (is_array($key)) {
			foreach ($key as $value) {
				$fixed[] = $this->config['memcache']['prefix'].$value;
			}
		} else {
			$fixed = $this->config['memcache']['prefix'].$key;
		}
		return Memcache::get($fixed, $flags);
	}

	/**
	 * Memcache Set with automatic prefixing.
	 *
	 * @access	public
	 * @param	string	Key for stored item
	 * @param	mixed	Item to store
	 * @param	mixed	Memcache flags
	 * @param	integer	Seconds until expiration
	 * @return	mixed
	 */
	public function set($key, $var, $flags, $expire) {
		return Memcache::set($this->config['memcache']['prefix'].$key, $var, $flags, $expire);
	}
}
?>