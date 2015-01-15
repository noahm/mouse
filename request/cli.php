<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Request CLI - Handles command line arguments, cleaning them, and inserting them into a HTTP compatible request object.
 *
 * @author 		Alexia E. Smith
 * @license		GNU General Public License v3
 * @package		Mouse Framework
 * @link		https://github.com/Alexia/mouse
 *
**/
namespace mouse\request;
use mouse;

class cli {
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
	 * @param	string	[Optional] Object key used to initialize the object to mouse.  Also serves as the settings array key.
	 * @return	void
	 */
	public function __construct($objectKey = 'cli') {
		$this->objectKey	= $objectKey;
		$this->settings		=& mouse\hole::$settings[$this->objectKey];

		global $argv;
		if (count($argv)) {
			foreach ($argv as $key => $value) {
				if ($key == 0) {
					$this->script_name = $value;
				} else {
					$this->get[$key] = $this->cleanRequestValue($value);
				}
			}
			$this->post		= $this->get;
			$this->request	= $this->get;
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
		} else {
			return $value;
		}
	}

	/**
	 * Sets up 'key' => $value references to 1 => $value.
	 *
	 * @access	public
	 * @param	array	Array of aliases
	 * @return	void
	 */
	public function setupAliases($aliases) {
		if (count($aliases) && is_array($aliases)) {
			foreach ($aliases as $key => $value) {
				$this->get[$value]		= $this->get[$key];
				$this->post[$value]		= $this->post[$key];
				$this->request[$value]	= $this->request[$key];
			}
		}
	}
}
?>