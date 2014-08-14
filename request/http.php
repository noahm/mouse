<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Request HTTP - Handles $_GET and $_POST variables, cleaning them, and inserting them into the request object.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2014 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/
namespace mouse\Request;
use mouse;

class Http {
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
	public function __construct($objectKey = 'http') {
		$this->objectKey	= $objectKey;
		$this->settings		=& mouse\Hole::$settings[$this->objectKey];

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
		if (is_numeric($value) && preg_match('#[\.|\+|-|e|E]#s', $value)) {
			return floatval($value);
		} elseif (is_numeric($value)) {
			return intval($value);
		} elseif (is_array($value) && count($value)) {
			foreach ($value as $subvalue) {
				$array[] = $this->cleanRequestValue($subvalue);
			}
			return $array;
		} else {
			return $value;
		}
	}
}
?>