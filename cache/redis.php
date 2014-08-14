<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Cache Redis - Interface to Redis, provides automatic connection setup.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2014 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		https://bitbucket.org/ashlawnk/mouse
 *
**/
namespace mouse\cache;
use mouse;

class redis {
	/**
	 * Redis Instance
	 *
	 * @var		object
	 */
	private static $instance;

	/**
	 * Redis Initialized Successfully
	 *
	 * @var		boolean
	 */
	public $redisInitialized = false;

	/**
	 * Memory cached Redis results.
	 *
	 * @var		array
	 */
	protected static $cache = [];

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
	 * @return	void
	 */
	public function __construct($objectKey = 'redis') {
		$this->objectKey	= $objectKey;
		$this->settings		=& mouse\hole::$settings[$this->objectKey];

		//Automatic enable.
		if ($this->settings['use_redis']) {
			$this->enabled	= $this->init();
		} else {
			$this->enabled	= false;
		}
	}

	/**
	 * Magic call function to map $mouse->redis->command() calls to $mouse->redis->redis->command().
	 *
	 * @access	public
	 * @param	string	Called magic function name.
	 * @param	array	Array of arguments.
	 * @return	mixed
	 */
	public function __call($function, $arguments) {
		if ($this->redisInitialized) {
			try {
				return call_user_func_array(array($this->redis, $function), $arguments);
			} catch (\Predis\Connection\ConnectionException $e) {
				// attempt to re-establish a connection before trashing the redis object completely
				if (!$this->redis->isConnected() && $this->redis->connect() && !$this->redis->isConnected()) {
					$this->redisInitialized = false;
					$this->redis = null;
				}
			} catch (\Predis\NotSupportedException $e) {
				$this->redisInitialized = false;
				$this->redis = null;
			} catch (\Exception $e) {
				$this->redisInitialized = false;
				$this->redis = null;
			}
		}
	}

	/**
	 * Automatically initiate Redis connection.
	 *
	 * @access	public
	 * @return	void
	 */
	public function init() {
		if ($this->redisInitialized) {
			return $this->redisInitialized;
		}

		if ($this->settings['use_redis'] == 1 && $this->settings['servers']) {
			include_once('Predis/Autoloader.php');
			\Predis\Autoloader::register();
			if (class_exists('\Predis\Client')) {
				$options = [];
				if ($this->settings['prefix']) {
					$options['prefix'] = $this->settings['prefix'];
				}
				$this->redis = new \Predis\Client($this->settings['servers'], $options);

				try {
					$this->redis->connect();
				} catch (\Predis\Connection\ConnectionException $e) {
					$this->redis = null;
				} catch (\Exception $e) {
					$this->redis = null;
				}

				if ($this->redis) {
					$this->redisInitialized = true;
				}
			}
		}
		return $this->redisInitialized;
	}

	/**
	 * Initialize Singleton
	 *
	 * @return	object
	 */
	static public function instance() {
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Sets serialized data into Redis.
	 *
	 * @access	public
	 * @param	string	Redis Key
	 * @param	mixed	Redis data to be serialized
	 * @return	mixed	Redis result.
	 */
	public function setSerialized($key, $data) {
		$data = serialize($data);

		$return = $this->set($key, $data);
		self::$cache[$key] = $data;

		return $return;
	}

	/**
	 * Gets unserialized data out of Redis.
	 *
	 * @access	public
	 * @param	string	Redis Key
	 * @return	mixed	Redis result.
	 */
	public function getUnserialized($key) {
		if (self::$cache[$key]) {
			return unserialize(self::$cache[$key]);
		} else {
			$data = $this->get($key);
			if ($data) {
				self::$cache[$key] = $data;
			}
			return unserialize($data);
		}
	}
}
?>