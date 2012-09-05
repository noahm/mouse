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
	 * Mouse Configuration Properties
	 *
	 * @var		array
	 */
	public static $config = array();

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
	public static $iteration = '20007';

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	array	[Optional] Array of configuration options.
	 * @param	array	[Optional] Array of object keys to classes to autoload.
	 * @return	void
	 */
	private function __construct($classes = array(), $config = array()) {
		//Define a constant mouse hole.
		define('MOUSE_DIR', dirname(__FILE__));

		spl_autoload_register(array(self, 'autoloadClass'), true, false);

		//Always load configuration first.  Some classes require configuration to be passed in first for successful setup.
		if (count($config)) {
			$this->loadConfig($config);
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
	 * Loads and sets up pointers to configuration information.
	 *
	 * @access	public
	 * @param	array	Array of settings.
	 * @return	void
	 */
	public function loadConfig($config = array()) {
		self::$config = array_merge(self::$config, $config);
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
				if (!$this->$key instanceof $className) {
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
	 * @param	array	[Optional] Array of configuration options.
	 * @param	array	[Optional] Array of object keys to classes to autoload.
	 * @return	object	mouseHole
	 */
	static public function instance($classes = array(), $config = array()) {
		if (!self::$instance) {
			self::$instance = new self($classes, $config);
		} else {
			//Always load configuration first.  Some classes require configuration to be passed in first for successful setup.
			self::$instance->loadConfig($config);
			//Reloop over provided classes to load an additional that may have been called on this pass.
			self::$instance->loadClasses($classes);
		}

		return self::$instance;
	}
}
?>