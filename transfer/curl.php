<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Transfer CURL - Wrapper functions for file transfer with CURL.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2014 NoName Studios
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
	 * Last debug output of request information.
	 *
	 * @var		mixed	Associative array of request information or false on failure.
	 */
	private $lastRequestInfo = false;

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	[Optional] Object key used to initialize the object to mouse.  Also serves as the settings array key.
	 * @return	void
	 */
	public function __construct($objectKey = 'curl') {
		$this->objectKey	= $objectKey;
		$this->settings		=& mouseHole::$settings[$this->objectKey];
	}

	/**
	 * CURL wrapper for get and post functionality. Some options are only configured by settings at load time:
	 * array('interface' => eth1, 'useragent' => 'Custom Agent/1.0') interface: Physical interface to use on the hardware level.  useragent: Replace the default Mouse Framework user agent string.
	 *
	 * @access	public
	 * @param	string	URL to CURL
	 * @param	array	[Optional] Post Fields, must be an array of key => value pairs.
	 * @param	array	[Optional] Options array('reuse' => false, 'headers' => array('cs-api-key: abcd123'))
	 *					reuse: Reuse connection for keep-alive, false by default.
	 *					headers: Array of http header strings
	 *					username/password: use when http auth is required
	 *					verb: the HTTP verb to use instead of GET (or POST)
	 * @param	boolean	Turn on various debug functionality such as saving information with the CURLINFO_HEADER_OUT option.
	 * @return	mixed	Raw page text/HTML or false for a 404/503 response.
	 */
	public function fetch($location, $postFields = array(), $options = array(), $debug = false) {
		if (!$ch) {
			$ch = curl_init();
		}
		$timeout = 10;

		if ($this->settings['useragent']) {
			$useragent = $this->settings['useragent'];
		} else {
			$useragent = "Mouse Framework/".mouseHole::$version;
		}

		$dateTime = gmdate("D, d M Y H:i:s", time())." GMT";
		$headers = array('Date: '.$dateTime);
		if (is_array($options['headers']) && count($options['headers'])) {
			$headers = array_merge($headers, $options['headers']);
		}

		$curl_options = [
			CURLOPT_TIMEOUT			=> $timeout,
			CURLOPT_USERAGENT		=> $useragent,
			CURLOPT_URL				=> $location,
			CURLOPT_CONNECTTIMEOUT	=> $timeout,
			CURLOPT_FOLLOWLOCATION	=> true,
			CURLOPT_MAXREDIRS		=> 10,
			CURLOPT_COOKIEFILE		=> '/tmp/curlget',
			CURLOPT_COOKIEJAR		=> '/tmp/curlget',
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_HTTPHEADER		=> $headers
		];

		if (isset($options['username']) && isset($options['password'])) {
			$curl_options[CURLOPT_USERPWD] = $options['username'].':'.$options['password'];
			$curl_options[CURLOPT_HTTPAUTH] = CURLAUTH_ANY;
		}

		if (is_array($postFields) && count($postFields)) {
			$curl_options[CURLOPT_POST]			= true;
			$curl_options[CURLOPT_POSTFIELDS]	= $postFields;
		}

		if (isset($options['verb'])) {
			$curl_options[CURLOPT_CUSTOMREQUEST] = $options['verb'];
		}

		if ($this->settings['interface']) {
			$curl_options[CURLOPT_INTERFACE]	= $this->settings['interface'];
		}

		curl_setopt_array($ch, $curl_options);

		if ($debug === true) {
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		}

		$page = curl_exec($ch);

		if ($debug === true) {
			$this->lastRequestInfo = curl_getinfo($ch);
		}

		$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($response_code == 503 || $response_code == 404) {
			return false;
		}

		if (!$options['reuse']) {
			curl_close($ch);
		}
		return $page;
	}

	/**
	 * Shortcut to fetch() with using a POST request instead of GET, even with empty post data
	 */
	public function post($location, $postFields = array(), $options = array(), $debug = false) {
		$options['verb'] = 'POST';
		if (empty($postFields)) {
			$options['headers'][] = 'Content-Length: 0';
		}
		return $this->fetch($location, $postFields, $options, $debug);
	}

	/**
	 * Return the last request information with debugging turned on.
	 *
	 * @access	public
	 * @return	mixed	Associative array of request information or false on failure.
	 */
	public function getLastRequestInfo() {
		return $this->lastRequestInfo;
	}
}
?>