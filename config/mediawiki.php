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

class mouseConfigMediawiki {
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
		$this->settings		=& mouseHole::$settings[$this->objectKey];

		if (!defined('MEDIAWIKI')) {
			define('MEDIAWIKI', 'WTF');
		}
		if (!defined('SETTINGS_ONLY')) {
			define('SETTINGS_ONLY', 'WTF');
		}
		require(mouseHole::$settings['file']);
		mouseHole::$settings['DB'] = array(
											'server'		=> $wgDBserver,
											'port'			=> $wgDBport,
											'database'		=> $wgDBname,
											'user'			=> $wgDBuser,
											'pass'			=> $wgDBpassword,
											'use_database'	=> true
										);

		if ($wgMetaNamespace) {
			mouseHole::$settings['wiki']['wiki_name']		= $wgSitename;
			mouseHole::$settings['wiki']['wiki_domain']		= str_ireplace(array('http://', 'https://'), '', $wgServer);
			mouseHole::$settings['wiki']['wiki_meta_name']	= $wgMetaNamespace;
			mouseHole::$settings['wiki']['wiki_database']	= $wgDBname;
		} else {
			throw new Exception('MediaWiki Meta Name $wgMetaNamespace is not defined.  Class '.__CLASS__.' requires this to continue.');
		}

		if (count($wgMemCachedServers)) {
			list($server, $port) = explode(':', $wgMemCachedServers[0]);
			foreach ($wgMemCachedServers as $server) {
				if (is_string($server)) {
					list($host, $port) = explode(':', $server);
					mouseHole::$settings['memcache']['servers'][] = array(
																			'host'	=> $host,
																			'port'	=> $port
																		);
				} elseif (is_array($server)) {
					list($host, $port) = explode(':', $server[0]);
					mouseHole::$settings['memcache']['servers'][] = array(
																			'host'		=> $host,
																			'port'		=> $port,
																			'weight'	=> $server[1]
																		);
				}
			}
			mouseHole::$settings['memcache']['use_memcache']	= true;
		}

		if ($redisCachingServers) {
			(is_array(mouseHole::$settings['redis']['servers']) ? mouseHole::$settings['redis']['servers'] = array_merge(mouseHole::$settings['redis']['servers'], $redisCachingServers) : mouseHole::$settings['redis']['servers'] = $redisCachingServers);
			mouseHole::$settings['redis']['prefix']		= MASTER_WIKI_META.':';
			mouseHole::$settings['redis']['use_redis']	= true;
		}
	}
}
?>