<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Cache Redis - Interface to Redis, provides automatic connection setup.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2012 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 *
**/

class mouseCacheRedis {
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
		if ($this->config['use_redis']) {
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
		include_once('Predis/Autoloader.php');
		Predis\Autoloader::register();
		if (class_exists('Predis\Client')) {
			$this->redis = new Predis\Client($this->config['redis_servers'], array('prefix' => $this->config['db']['sql_database'].':', 'throw_errors' => false));
			try {
				$this->redis->connect();
				$this->redisInitialized = true;
			} catch (Predis\Network\ConnectionException $e) {
				//Nothing!
			}
		}
		
		return $this->redisInitialized;
	}
}
?>