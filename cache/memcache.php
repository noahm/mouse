<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Cache Memcache - Interface to Memcache, provides automatic connection setup.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2013 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/

class mouseCacheMemcache extends Memcache {
	/**
	 * Memcache Connection
	 *
	 * @var		resource
	 */
	private $memcache_link;

	/**
	 * Memory cached Memcache results.
	 *
	 * @var		array
	 */
	protected $RAMcache = array();

	/**
	 * Flag to enable memory cached Memcache results.
	 *
	 * @var		boolean
	 */
	protected $useRAMCache = true;

	/**
	 * Object Key
	 *
	 * @var		object
	 */
	public $objectKey;

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	string	[Optional] Object key used to initialize the object to mouse.  Also serves as the settings array key.
	 * @param	boolean	[Optional] Store Memcache results in local RAM for faster look up.  Default is true.
	 * @return	void
	 */
	public function __construct($objectKey = 'memcache') {
		$this->objectKey	= $objectKey;
		$this->settings		=& mouseHole::$settings[$this->objectKey];

		//Automatic enable.
		if ($this->settings['use_memcache']) {
			$this->enabled	= $this->init();
		} else {
			$this->enabled	= false;
		}

		if (is_bool($this->settings['use_ramcache'])) {
			$this->useRAMCache = $this->settings['use_ramcache'];
		}
	}

	/**
	 * Magic call function to map $mouse->memcache->command() calls to Memcache::command().
	 *
	 * @access	public
	 * @param	string	Called magic function name.
	 * @param	array	Array of arguments.
	 * @return	mixed
	 */
	public function __call($function, $arguments) {
		return call_user_func_array(array('Memcache', $function), $arguments);
	}

	/**
	 * Automatically initiate memcache connection.
	 *
	 * @access	public
	 * @return	void
	 */
	public function init() {
		return @$this->connect($this->settings['server'], $this->settings['port']);
	}

	/**
	 * Enables the local RAM cache.
	 *
	 * @access	public
	 * @return	void
	 */
	public function enableRAMCache() {
		$this->useRAMCache = true;
	}

	/**
	 * Disables the local RAM cache.
	 *
	 * @access	public
	 * @return	void
	 */
	public function disableRAMCache() {
		$this->useRAMCache = false;
	}

	/**
	 * Flushes the local RAM cache.
	 *
	 * @access	public
	 * @return	void
	 */
	public function flushRAMCache() {
		$this->RAMCache = array();
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
		return Memcache::add($this->settings['prefix'].$key, $var, $flags, $expire);
	}

	/**
	 * Memcache Delete with automatic prefixing.
	 *
	 * @access	public
	 * @param	string	Key for stored item
	 * @return	mixed
	 */
	public function delete($key) {
		return Memcache::delete($this->settings['prefix'].$key);
	}

	/**
	 * Memcache Get with automatic prefixing.
	 *
	 * @access	public
	 * @param	mixed	Key string or array of key strings for stored item(s)
	 * @param	mixed	[Optional] Memcache flags
	 * @return	mixed
	 */
	public function get($key, $flags = null) {
		if (is_array($key)) {
			//We do not use the RAM cache for arrays of keys.  This is because the possibility of only having part of those keys in the RAM cache would cause additional Memcache::get calls thus negating any performance gains.
			foreach ($key as $value) {
				$prefixedKey[] = $this->settings['prefix'].$value;
			}
		} else {
			$prefixedKey = $this->settings['prefix'].$key;
			if ($this->useRAMCache and array_key_exists($prefixedKey, $this->RAMcache)) {
				if ($this->RAMcache[$prefixedKey]['expire'] < time()) {
					return $this->RAMcache[$prefixedKey]['value'];
				} else {
					unset($this->RAMcache[$prefixedKey]);
				}
			}
		}

		return Memcache::get($prefixedKey, $flags);
	}

	/**
	 * Memcache Set with automatic prefixing.
	 *
	 * @access	public
	 * @param	string	Key for stored item
	 * @param	mixed	Item to store
	 * @param	mixed	Memcache flags
	 * @param	integer	Seconds until expiration
	 * @return	boolean	True on success, false on failure.
	 */
	public function set($key, $var, $flags, $expire) {
		$prefixedKey = $this->settings['prefix'].$key;

		$return = Memcache::set($prefixedKey, $var, $flags, $expire);

		if ($return and $this->useRAMCache) {
			$this->RAMcache[$prefixedKey] = array(
													'value' => $var,
													'expire' => ($expire <= 2592000 ? time() + $expire : $expire)
												);
		}

		return $return;
	}

	/**
	 * Memcache Decrement with automatic prefixing.
	 *
	 * @access	public
	 * @param	string	Key for stored item
	 * @param	integer	[Optional] Amount to decrease by.
	 * @return	mixed	Modified value of the key or false on failure.
	 */
	public function decrement($key, $amount = -1) {
		$prefixedKey = $this->settings['prefix'].$key;

		$return = Memcache::decrement($prefixedKey, $amount);

		if ($this->useRAMCache and array_key_exists($prefixedKey, $this->RAMcache)) {
			if ($this->RAMcache[$prefixedKey]['expire'] < time() and $return !== false) {
				$this->RAMcache[$prefixedKey]['value'] = $return;
			} else {
				unset($this->RAMcache[$prefixedKey]);
			}
		}

		return $return;
	}

	/**
	 * Memcache Increment with automatic prefixing.
	 *
	 * @access	public
	 * @param	string	Key for stored item
	 * @param	integer	[Optional] Amount to increase by.
	 * @return	mixed	Modified value of the key or false on failure.
	 */
	public function increment($key, $amount = 1) {
		$prefixedKey = $this->settings['prefix'].$key;

		$return = Memcache::increment($prefixedKey, $amount);

		if ($this->useRAMCache and array_key_exists($prefixedKey, $this->RAMcache)) {
			if ($this->RAMcache[$prefixedKey]['expire'] < time() and $return !== false) {
				$this->RAMcache[$prefixedKey]['value'] = $return;
			} else {
				unset($this->RAMcache[$prefixedKey]);
			}
		}

		return $return;
	}
}
?>