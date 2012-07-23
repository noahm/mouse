<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Request HTTP - Handles $_GET and $_POST variables, cleaning them, and inserting them into the request object.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2012 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/

class mouseRequestHttp {
	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct($mouse) {
		foreach ($_GET as $key => $value) {
			$this->get[$key] = $this->cleanRequestValue($value);
		}

		foreach ($_POST as $key => $value) {
			$this->post[$key] = $this->cleanRequestValue($value);
		}

		foreach ($_REQUEST as $key => $value) {
			$this->request[$key] = $this->cleanRequestValue($value);
		}
	}

	/**
	 * Cleans Request Values for safe usage.
	 *
	 * @access	public
	 * @param	string	Uncleaned value
	 * @return	mixed	Cleaned value
	 */
	private function cleanRequestValue($value) {
		if (is_numeric($value) AND preg_match('#[\.|\+|-|e|E]#s', $value)) {
			return floatval($value);
		} elseif (is_numeric($value)) {
			return intval($value);
		} else {
			return $value;
		}
	}
}
?>