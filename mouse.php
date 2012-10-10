<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse - Main Class
 *
 * @author		Alexia E. Smith
 * @copyright	(c) 2010 - 2012 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/

class mouseHole {
	/**
	 * Mouse Instance
	 *
	 * @var		object
	 */
	private static $instance;

	/**
	 * Global settings assigned by key words.
	 *
	 * @var		array
	 */
	public static $settings = array();

	/**
	 * Reserved Key Words
	 *
	 * @var		array
	 */
	private static $reservedKeys = array('settings', 'version', 'iteration', 'instance');

	/**
	 * Mouse Framework Version
	 *
	 * @var		string
	 */
	public static $version = '2.0';

	/**
	 * Mouse Framework Iteration
	 *
	 * @var		string
	 */
	public static $iteration = '20011';

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

	/**
	 * Clone - No Clones Allowed
	 *
	 * @access	public
	 * @return	void
	 */
	private function __clone() {
		//"We've had cloning in the South for years. It's called cousins." --Robin Williams, actor
	}

	/**
	 * Loads and sets up pointers to settings information.
	 *
	 * @access	public
	 * @param	array	Array of settings.
	 * @return	void
	 */
	public function loadSettings($settings = array()) {
		if (!is_array(self::$settings)) {
			//Reseting the settings array if it gets messed up.
			self::$settings = array();
		}
		if ($settings and is_array($settings)) {
			self::$settings = array_merge(self::$settings, $settings);
		}
	}

	/**
	 * Loads and setups classes as part of the mouse object.
	 *
	 * @access	public
	 * @param	array	Array of object keys to classes to autoload.
	 * @return	void
	 */
	public function loadClasses($classes) {
		if (count($classes)) {
			foreach ($classes as $key => $className) {
				if (in_array($key, self::$reservedKeys)) {
					throw new Exception("Mouse modules can not be assigned to certain reserved key words.  Attempted to load: {$key} => {$className}");
				}
				if (!property_exists($this, $key) or !$this->$key instanceof $className) {
					$this->$key = new $className($key);
				}
			}
		}
	}

	/**
	 * Autoloader
	 *
	 * @access	public
	 * @param	string	Class name to load automatically.
	 * @return	void
	 */
	public function autoloadClass($classname) {
		$file = MOUSE_DIR.preg_replace_callback('#([A-Z])#s', function ($match) { return DIRECTORY_SEPARATOR.strtolower($match[1]); }, str_replace('mouse', '', $classname)).'.php';
		if (is_file($file)) {
			require_once($file);
		}
	}

	/**
	 * Setup a new mouse() instance or return the existing instance.
	 *
	 * @access	public
	 * @param	array	[Optional] Array of object keys to classes to autoload.
	 * @param	array	[Optional] Array of settings.
	 * @return	object	mouseHole
	 */
	static public function instance($classes = array(), $settings = array()) {
		if (!self::$instance) {
			self::$instance = new self($classes, $settings);
		} else {
			//Always load settings first.  Some classes require settings to be passed in first for successful setup.
			self::$instance->loadSettings($settings);
			//Reloop over provided classes to load an additional that may have been called on this pass.
			self::$instance->loadClasses($classes);
		}

		return self::$instance;
	}
}
?>