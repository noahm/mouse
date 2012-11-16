<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Database Module
 *
 * @author		Alexia E. Smith
 * @copyright	(c) 2010 - 2013 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/

class mouseDatabaseModule {
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	array	[Optional] Array of object keys to classes to autoload.
	 * @param	array	[Optional] Array of settings.
	 * @return	void
	 */
	private function __construct($classes = array(), $settings = array()) {
		//Define a constant mouse hole.
		define('MOUSE_DIR', dirname(__FILE__));

		spl_autoload_register(array($this, 'autoloadClass'), true, false);

		//Always load settings first.  Some classes require settings to be passed in first for successful setup.
		if (count($settings)) {
			$this->loadSettings($settings);
		}

		if (count($classes)) {
			$this->loadClasses($classes);
		}
	}
}
?>