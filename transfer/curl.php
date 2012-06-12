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
 *
**/

class mouseTransferCURL {
	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct($mouse) {
		$this->config	=& mouseHole::$config;
	}

	/**
	 * Downloads the contents of a HTML page.
	 *
	 * @access	public
	 * @param	string	HTML page to curl
	 * @return	mixed	Raw page text/HTML or false for a 404 response.
	 */
	public function curlGet($location) {
		$ch = curl_init();
		$timeout = 10;
		$useragent = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.13";
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		curl_setopt($ch, CURLOPT_URL, $location);
		curl_setopt($ch, CURLOPT_COOKIE, '');
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
		curl_setopt($ch, CURLOPT_COOKIEFILE, '/dev/null');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$page = curl_exec($ch);

		$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($response_code == 404) {
			return false;
		}

		curl_close($ch);
		return $page;
	}
}
?>