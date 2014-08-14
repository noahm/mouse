<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Config Mediawiki - Converts a Mediawiki LocalSettings.php file into a mouse compatible configuration.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2014 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/
namespace mouse\config;
use mouse;

class Mediawiki {
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
	 * @param	[Optional] Object key used to initialize the object to mouse.  Also serves as the settings array key.
	 * @return	void
	 */
	public function __construct($objectKey = 'mediawiki') {
		//This Mediawiki configuration object is a prototype bridge between a Mediawiki LocalSettings.php file and the immature mouse configuration concept.
		$this->objectKey	= $objectKey;
		$this->settings		=& mouse\hole::$settings[$this->objectKey];

		if (!defined('MEDIAWIKI')) {
			define('MEDIAWIKI', 'WTF');
		}
		if (!defined('SETTINGS_ONLY')) {
			define('SETTINGS_ONLY', 'WTF');
		}
		require(mouse\hole::$settings['file']);
		mouse\hole::$settings['DB'] = array(
											'server'		=> $wgDBserver,
											'port'			=> $wgDBport,
											'database'		=> $wgDBname,
											'user'			=> $wgDBuser,
											'pass'			=> $wgDBpassword,
											'use_database'	=> true
										);

		if ($wgMetaNamespace) {
			mouse\hole::$settings['wiki']['wiki_name']		= $wgSitename;
			mouse\hole::$settings['wiki']['wiki_domain']		= str_ireplace(array('http://', 'https://'), '', $wgServer);
			mouse\hole::$settings['wiki']['wiki_meta_name']	= $wgMetaNamespace;
			mouse\hole::$settings['wiki']['wiki_database']	= $wgDBname;
		} else {
			throw new \Exception('MediaWiki Meta Name $wgMetaNamespace is not defined.  Class '.__CLASS__.' requires this to continue.');
		}

		if (count($wgMemCachedServers)) {
			list($server, $port) = explode(':', $wgMemCachedServers[0]);
			foreach ($wgMemCachedServers as $server) {
				if (is_string($server)) {
					list($host, $port) = explode(':', $server);
					mouse\hole::$settings['memcache']['servers'][] = array(
																			'host'	=> $host,
																			'port'	=> $port
																		);
				} elseif (is_array($server)) {
					list($host, $port) = explode(':', $server[0]);
					mouse\hole::$settings['memcache']['servers'][] = array(
																			'host'		=> $host,
																			'port'		=> $port,
																			'weight'	=> $server[1]
																		);
				}
			}
			mouse\hole::$settings['memcache']['use_memcache']	= true;
		}

		if ($redisCachingServers) {
			(is_array(mouse\hole::$settings['redis']['servers']) ? mouse\hole::$settings['redis']['servers'] = array_merge(mouse\hole::$settings['redis']['servers'], $redisCachingServers) : mouse\hole::$settings['redis']['servers'] = $redisCachingServers);
			mouse\hole::$settings['redis']['prefix']		= MASTER_WIKI_META.':';
			mouse\hole::$settings['redis']['use_redis']	= true;
		}
	}
}
?>