<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Cache Memcache - Interface to Memcache, provides automatic connection setup.
 *
 * @author 		Alexia E. Smith
 * @license		GNU General Public License v3
 * @package		Mouse Framework
 * @link		https://github.com/Alexia/mouse
 *
**/
namespace mouse\cache;
use mouse;

class memcache extends \Memcache {
	/**
	 * Memory cached Memcache results.
	 *
	 * @var		array
	 */
	protected $RAMcache = [];

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
		$this->settings		=& mouse\hole::$settings[$this->objectKey];

		//Automatic enable.
		if ($this->settings['use_memcache']) {
			$this->init();
		}

		if (is_bool($this->settings['use_ramcache'])) {
			$this->useRAMCache = $this->settings['use_ramcache'];
		}
	}

	/**
	 * Automatically initiate memcache connection.  Utilizes the addServer() function with arguments named the same in the settings.  At least one server must be valid to return true.
	 *
	 * @access	public
	 * @return	boolean	Connection Success
	 */
	public function init() {
		$return = false;
		if (is_array($this->settings['servers']) && count($this->settings['servers']) && array_key_exists('host', $this->settings['servers'][0])) {
			foreach ($this->settings['servers'] as $server) {
				//So, I wanted to be fancy and use this line, but if the programmer puts the information out of order in the array then the arguments will be out of order.  "call_user_func_array(array($this, 'addServer'), $host);"
				$success = $this->addServer(
											$server['host'],
											(is_numeric($server['port']) ? intval($server['port']) : 11211),
											(is_bool($server['persistent']) ? $server['persistent'] : true),
											(is_numeric($server['weight']) ? intval($server['weight']) : 1),
											(is_numeric($server['timeout']) ? intval($server['timeout']) : 1),
											(is_numeric($server['retry_interval']) ? intval($server['retry_interval']) : 15),
											(is_bool($server['status']) ? intval($server['status']) : 15),
											(is_array($server['failure_callback']) ? $server['failure_callback'] : null)
											);
				if ($success == true) {
					$return = true;
				}
			}
		}

		$this->enabled = $return;

		return $return;
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
		$this->RAMCache = [];
	}

	/**
	 * Memcache Add with automatic prefixing.
	 *
	 * @access	public
	 * @param	string	Key for stored item
	 * @param	mixed	Item to store
	 * @param	mixed	[Optional] Memcache flags
	 * @param	integer	[Optional] Seconds until expiration
	 * @return	mixed
	 */
	public function add($key, $var, $flags = null, $expire = 0) {
		$prefixedKey = $this->settings['prefix'].$key;

		$return = parent::add($prefixedKey, $var, $flags, $expire);

		if ($return && $this->useRAMCache) {
			$this->RAMcache[$prefixedKey] = array(
													'value' => $var,
													'expire' => ($expire <= 2592000 ? time() + $expire : $expire)
												);
		}

		return $return;
	}

	/**
	 * Memcache Delete with automatic prefixing.
	 *
	 * @access	public
	 * @param	string	Key for stored item
	 * @return	mixed
	 */
	public function delete($key) {
		return parent::delete($this->settings['prefix'].$key);
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
			if ($this->useRAMCache && array_key_exists($prefixedKey, $this->RAMcache)) {
				if ($this->RAMcache[$prefixedKey]['expire'] < time()) {
					return $this->RAMcache[$prefixedKey]['value'];
				} else {
					unset($this->RAMcache[$prefixedKey]);
				}
			}
		}

		return parent::get($prefixedKey, $flags);
	}

	/**
	 * Memcache Set with automatic prefixing.
	 *
	 * @access	public
	 * @param	string	Key for stored item
	 * @param	mixed	Item to store
	 * @param	mixed	[Optional] Memcache flags
	 * @param	integer	[Optional] Seconds until expiration
	 * @return	boolean	True on success, false on failure.
	 */
	public function set($key, $var, $flags = null, $expire = 0) {
		$prefixedKey = $this->settings['prefix'].$key;

		$return = parent::set($prefixedKey, $var, $flags, $expire);

		if ($return && $this->useRAMCache) {
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

		$return = parent::decrement($prefixedKey, $amount);

		if ($this->useRAMCache && array_key_exists($prefixedKey, $this->RAMcache)) {
			if ($this->RAMcache[$prefixedKey]['expire'] < time() && $return !== false) {
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

		$return = parent::increment($prefixedKey, $amount);

		if ($this->useRAMCache && array_key_exists($prefixedKey, $this->RAMcache)) {
			if ($this->RAMcache[$prefixedKey]['expire'] < time() && $return !== false) {
				$this->RAMcache[$prefixedKey]['value'] = $return;
			} else {
				unset($this->RAMcache[$prefixedKey]);
			}
		}

		return $return;
	}
}
?>