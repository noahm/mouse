<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Config Mediawiki - Converts a Mediawiki LocalSettings.php file into a mouse compatible configuration.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2012 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/

class mouseConfigMediawiki {
	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct($mouse) {
		$this->config	=& mouseHole::$config;

		define('MEDIAWIKI', 'WTF');
		define('SETTINGS_ONLY', 'WTF');
		require_once($this->config['file']);
		$this->config['db'] = array(
									'server'	=> $wgDBserver,
									'port'		=> $wgDBport,
									'database'	=> $wgDBname,
									'user'		=> $wgDBuser,
									'pass'		=> $wgDBpassword
									);
		$this->config['use_database'] = true;

		if (count($wgMemCachedServers)) {
			list($server, $port) = explode(':', $wgMemCachedServers[0]);
			$this->config['memcache']['server']	= $server;
			$this->config['memcache']['port']	= $port;
			$this->config['use_memcache'] = true;
		}

		if ($redisCachingServers) {
			$this->config['redis_servers']	= $redisCachingServers;
			$this->config['use_redis']		= false;
		}
	}
}
?>