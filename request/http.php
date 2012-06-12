<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Request - Handles $_GET and $_POST variables, cleaning them, and inserting them into the request object.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2012 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
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
			if (is_numeric($value) AND preg_match('#[\.|\+|-|e|E]#s', $value)) {
				$this->get[$key] = floatval($value);
			} elseif (is_numeric($value)) {
				$this->get[$key] = intval($value);
			} else {
				$this->get[$key] = $value;
			}
		}
		foreach ($_POST as $key => $value) {
			if (is_numeric($value) AND preg_match('#[\.|\+|-|e|E]#s', $value)) {
				$this->post[$key] = floatval($value);
			} elseif (is_numeric($value)) {
				$this->post[$key] = intval($value);
			} else {
				$this->post[$key] = $value;
			}
		}
	}
}
?>