<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse - Main Class
 *
 * @author		Alexia E. Smith
 * @copyright	(c) 2010 - 2014 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/

namespace mouse;

class Hole {
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
	public static $settings = [];

	/**
	 * Reserved Key Words - Generally anything defined up here before the functions.
	 *
	 * @var		array
	 */
	private static $reservedKeys = ['settings', 'version', 'iteration', 'instance'];

	/**
	 * Currently loaded classes.
	 *
	 * @var		array
	 */
	private $loadedClasses = [];

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
	private function __construct($classes = [], $settings = []) {
		//Define a constant mouse hole.
		define('MOUSE_DIR', dirname(__FILE__));

		spl_autoload_register([$this, 'autoloadClass'], true, false);

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
	public function loadSettings($settings) {
		if (!is_array(self::$settings)) {
			//Reseting the settings array if it gets messed up.
			self::$settings = [];
		}
		if (count($settings) && is_array($settings)) {
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
					throw new \Exception("Mouse modules can not be assigned to certain reserved key words.  Attempted to load: {$key} => {$className}");
				}
				if (!property_exists($this, $key) || !$this->$key instanceof $className) {
					$this->$key = new $className($key);
					$this->loadedClasses[$key] = $className;
				}
			}
		}
	}

	/**
	 * Returns the first object key found for a class name.
	 *
	 * @access	public
	 * @param	string	Class name to search for on $this->loadClasses.
	 * @return	mixed	Object or Null
	 */
	public function getClassByName($className) {
		$key = array_search($className, $this->loadedClasses);

		if ($key && property_exists($this, $key) && $this->$key instanceof $className) {
			return $this->$key;
		}
		return null;
	}

	/**
	 * Returns alls object keys found for a class name in an array.
	 *
	 * @access	public
	 * @param	string	Class name to search for on $this->loadClasses.
	 * @return	mixed	Array of Objects or Null
	 */
	public function getClassesByName($className) {
		$keys = array_keys($this->loadedClasses, $className);

		if (count($keys)) {
			foreach ($keys as $key) {
				if ($key && property_exists($this, $key) && $this->$key instanceof $className) {
					$objects[] = $this->$key;
				}
			}
			if (count($objects)) {
				return $objects;
			}
		}
		return null;
	}

	/**
	 * Autoloader
	 *
	 * @access	public
	 * @param	string	Class name to load automatically.
	 * @return	void
	 */
	public function autoloadClass($classname) {
		$file = MOUSE_DIR.strtolower(str_replace('\\', DIRECTORY_SEPARATOR, str_replace('mouse', '', $classname))).'.php';
		$_file = MOUSE_DIR.preg_replace_callback(
			'#([A-Z])#s',
			function ($match) {
				return DIRECTORY_SEPARATOR.strtolower($match[1]);
			},
			str_replace('mouse', '', $classname)
		).'.php';
		if (is_file($file)) {
			require_once($file);
		} else {
			trigger_error(__CLASS__.": Class {$classname} not found at {$file}.", E_USER_WARNING);
		}
	}

	/**
	 * Setup a new mouse() instance or return the existing instance.
	 *
	 * @access	public
	 * @param	array	[Optional] Array of object keys to classes to autoload.
	 * @param	array	[Optional] Array of settings.
	 * @return	object	Hole
	 */
	static public function instance($classes = [], $settings = []) {
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