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
	public static $config;

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	array	Array of configuration options.
	 * @param	array	Array of object keys to classes to autoload.
	 * @return	void
	 */
	public function __construct($config, $classes = array()) {
		//Define a constant mouse hole.
		define('MOUSE_DIR', dirname(__FILE__));

		self::$config = $config;

		spl_autoload_register(array(self, 'autoloadClass'), true, false);

		if (count($classes)) {
			$this->loadClasses($classes);
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
				$this->$key = new $className(self);
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
	 * @param	array	Array of configuration options.
	 * @param	array	Array of object keys to classes to autoload.
	 * @return	object	mouseHole
	 */
	static public function instance($config, $classes = array()) {
		if (!self::$instance) {
			self::$instance = new self($config, $classes);
		}

		return self::$instance;
	}
}
?>