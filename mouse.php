<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse - Main Class
 *
 * @author		Alexia E. Smith
 * @license		GNU General Public License v3
 * @package		Mouse Framework
 * @link		https://github.com/Alexia/mouse
 *
**/

namespace mouse;

class hole {
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
	private $loadedModules = [];

	/**
	 * Mouse Framework Version
	 *
	 * @var		string
	 */
	public static $version = '3.0';

	/**
	 * Mouse Framework Iteration
	 *
	 * @var		string
	 */
	public static $iteration = '30000';

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	array	[Optional] Array of object keys to classes to autoload.
	 * @param	array	[Optional] Array of settings.
	 * @return	void
	 */
	private function __construct($modules = [], $settings = []) {
		//Define a constant mouse hole.
		define('MOUSE_DIR', __DIR__);

		spl_autoload_register([$this, 'autoloadClass'], true, false);

		//Always load settings first.  Some classes require settings to be passed in first for successful setup.
		if (count($settings)) {
			$this->loadSettings($settings);
		}

		if (count($modules)) {
			$this->loadModules($modules);
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
	public function loadModules($modules) {
		if (count($modules)) {
			foreach ($modules as $key => $moduleName) {
				if (in_array($key, self::$reservedKeys)) {
					throw new \Exception("Mouse modules can not be assigned to certain reserved key words.  Attempted to load: {$key} => {$moduleName}");
				}
				if (!property_exists($this, $key) || !$this->$key instanceof $moduleName) {
					$this->$key = new $moduleName($key);
					$this->loadedModules[$key] = $moduleName;
				}
			}
		}
	}

	/**
	 * Returns the first object key found for a class name.
	 *
	 * @access	public
	 * @param	string	Module name to search for on $this->loadedModules.
	 * @return	mixed	Object or Null
	 */
	public function getModuleByName($moduleName) {
		$key = array_search($moduleName, $this->loadedModules);

		if ($key && property_exists($this, $key) && $this->$key instanceof $moduleName) {
			return $this->$key;
		}
		return null;
	}

	/**
	 * Returns alls object keys found for a class name in an array.
	 *
	 * @access	public
	 * @param	string	Class name to search for on $this->loadModules.
	 * @return	mixed	Array of Objects or Null
	 */
	public function getModulesByName($moduleName) {
		$keys = array_keys($this->loadedModules, $moduleName);

		if (count($keys)) {
			foreach ($keys as $key) {
				if ($key && property_exists($this, $key) && $this->$key instanceof $moduleName) {
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
		$file = MOUSE_DIR.str_replace('\\', DIRECTORY_SEPARATOR, str_replace('mouse', '', $classname)).'.php';

		if (!is_file($file)) {
			//Attempt to load through legacy naming fallback.  If successful, toss an E_USER_WARNING.
			$file = MOUSE_DIR.preg_replace_callback(
				'#([A-Z])#s',
				function ($match) {
					return DIRECTORY_SEPARATOR.strtolower($match[1]);
				},
				str_replace('mouse', '', $classname)
			).'.php';
			if (is_file($file)) {
				throw new \Exception(__CLASS__.": Legacy style class name '{$classname}' detected.  Please update this class name statement to use the new namespaced format.");
			}
		}
		if (is_file($file)) {
			require_once($file);
		} else {
			throw new \Exception(__CLASS__.": Class file for {$classname} not found at {$file}.");
		}
	}

	/**
	 * Setup a new mouse() instance or return the existing instance.
	 *
	 * @access	public
	 * @param	array	[Optional] Array of object keys to classes to autoload.
	 * @param	array	[Optional] Array of settings.
	 * @return	object	hole
	 */
	static public function instance($modules = [], $settings = []) {
		if (!self::$instance) {
			self::$instance = new self($modules, $settings);
		} else {
			//Always load settings first.  Some classes require settings to be passed in first for successful setup.
			self::$instance->loadSettings($settings);
			//Reloop over provided classes to load an additional that may have been called on this pass.
			self::$instance->loadModules($modules);
		}

		return self::$instance;
	}
}
?>