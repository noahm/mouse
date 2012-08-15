<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Transfer CURL - Wrapper functions for file transfer with CURL.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2012 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/

class mouseTransferCurl {
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
	public function __construct($objectKey = 'curl') {
		$this->objectKey	= $objectKey;
		$this->config		=& mouseHole::$config[$this->objectKey];
	}

	/**
	 * CURL wrapper for get and post functionality.
	 *
	 * @access	public
	 * @param	string	URL to CURL
	 * @param	array	[Optional] Post Fields, must be an array of key => value pairs.
	 * @param	array	[Optional] Options array('reuse' => false, 'interface' => eth1, 'useragent' => 'Custom Agent/1.0') reuse: Reuse connection for keep-alive, false by default.  interface: Physical interface to use on the hardware level.  useragent: Replace the default Mouse Framework user agent string.
	 * @return	mixed	Raw page text/HTML or false for a 404/503 response.
	 */
	public function fetch($location, $postFields = array(), $options = array()) {
		if (!$ch) {
			$ch = curl_init();
		}
		$timeout = 10;

		if ($this->config['useragent']) {
			$useragent = $this->config['useragent'];
		} else {
			$useragent = "Mouse Framework/".mouseHole::$version;
		}

		$dateTime = gmdate("D, d M Y H:i:s", time())." GMT";

		$curl_options = array(	CURLOPT_TIMEOUT			=> $timeout,
								CURLOPT_USERAGENT		=> $useragent,
								CURLOPT_URL				=> $location,
								CURLOPT_CONNECTTIMEOUT	=> $timeout,
								CURLOPT_FOLLOWLOCATION	=> true,
								CURLOPT_MAXREDIRS		=> 4,
								CURLOPT_COOKIEFILE		=> '/tmp/curlget',
								CURLOPT_COOKIEJAR		=> '/tmp/curlget',
								CURLOPT_RETURNTRANSFER	=> true,
								CURLOPT_HTTPHEADER		=> array('Date: '.$dateTime)
							);

		if (count($postFields)) {
			$curl_options[CURLOPT_POST]			= true;
			$curl_options[CURLOPT_POSTFIELDS]	= $postFields;
		}

		if ($this->config['interface']) {
			$curl_options[CURLOPT_INTERFACE]	= $this->config['interface'];
		}

		curl_setopt_array($ch, $curl_options);

		$page = curl_exec($ch);

		$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($response_code == 503 or $response_code == 404) {
			return false;
		}

		if (!$options['reuse']) {
			curl_close($ch);
		}
		return $page;
	}
}
?>