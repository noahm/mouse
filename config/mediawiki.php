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
	 * Object Key
	 *
	 * @var		object
	 */
	public $objectKey;

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	[Optional] Object key used to initialize the object to mouse.  Also servers as the configuration array key.
	 * @return	void
	 */
	public function __construct($objectKey = 'mediawiki') {
		//This Mediawiki configuration object is a prototype bridge between a Mediawiki LocalSettings.php file and the immature mouse configuration concept.
		$this->objectKey	= $objectKey;
		$this->config		=& mouseHole::$config[$this->objectKey];

		if (!defined('MEDIAWIKI')) {
			define('MEDIAWIKI', 'WTF');
		}
		if (!defined('SETTINGS_ONLY')) {
			define('SETTINGS_ONLY', 'WTF');
		}
		require(mouseHole::$config['file']);
		mouseHole::$config['DB'] = array(
											'server'		=> $wgDBserver,
											'port'			=> $wgDBport,
											'database'		=> $wgDBname,
											'user'			=> $wgDBuser,
											'pass'			=> $wgDBpassword,
											'use_database'	=> true
										);

		if ($wgMetaNamespace) {
			mouseHole::$config['wiki_meta_namespace'] = $wgMetaNamespace;
		} else {
			throw new Exception('MediaWiki Meta Name $wgMetaNamespace is not defined.  Class '.__CLASS__.' requires this to continue.');
		}

		if (count($wgMemCachedServers)) {
			list($server, $port) = explode(':', $wgMemCachedServers[0]);
			mouseHole::$config['memcache']['server']		= $server;
			mouseHole::$config['memcache']['port']			= $port;
			mouseHole::$config['memcache']['use_memcache']	= true;
		}

		if ($redisCachingServers) {
			(is_array(mouseHole::$config['redis']['servers']) ? mouseHole::$config['redis']['servers'] = array_merge(mouseHole::$config['redis']['servers'], $redisCachingServers) : mouseHole::$config['redis']['servers'] = $redisCachingServers);
			mouseHole::$config['redis']['prefix']		= MASTER_WIKI_META.':';
			mouseHole::$config['redis']['use_redis']	= true;
		}

		mouseHole::$config['aes_key']	= $AESkey;
		mouseHole::$config['aes_iv']	= $AESIV;
	}
}
?>