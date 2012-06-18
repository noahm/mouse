<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Request CLI - Handles command line arguments, cleaning them, and inserting them into a HTTP compatible request object.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2012 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/

class mouseRequestCli {
	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct($mouse) {
		global $argv;
		foreach ($argv as $key => $value) {
			if ($key == 0) {
				$this->script_name = $value;
			} else {
				if (is_numeric($value) AND preg_match('#[\.|\+|-|e|E]#s', $value)) {
					$this->get[$key] = floatval($value);
				} elseif (is_numeric($value)) {
					$this->get[$key] = intval($value);
				} else {
					$this->get[$key] = $value;
				}
			}
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
		foreach ($aliases as $key => $value) {
			$this->get[$value] = $this->get[$key];
		}
	}
}
?>