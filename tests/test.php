<?php
/**
 * MouseTest
 * MouseTest Loader
 *
 * @author		Alexia E. Smith
 * @copyright	(c) 2014 Curse Inc.
 * @license		All Rights Reserved
 * @package		MouseTest
 * @link		http://www.curse.com/
 *
 **/

class mouseTest {
	/**
	 * Test Initialized
	 *
	 * @var		boolean
	 */
	static private $initialized = false;

	/**
	 * Mouse Object
	 *
	 * @var		object
	 */
	static private $mouse = null;

	/**
	 * Loads Mouse with a default set of modules.
	 *
	 * @access	private
	 * @return	void
	 */
	static private function loadMouse() {
		if (self::$initialized === false) {
			if (!class_exists('\mouse\Hole')) {
				require_once(dirname(__DIR__).'/mouse.php');
			}
			//The mouseConfigMediawiki module has to load before any modules that require configuration data from the LocalSettings file.
			self::$mouse = \mouse\Hole::instance(['output' => 'mouse\output\output', 'request' => 'mouse\request\http', 'DB' => 'mouse\database\mysqli', 'redis' => 'mouse\cache\redis'], $settings);

			self::$initialized = true;
		}
	}

	/**
	 * Returns the Mouse implementation.
	 *
	 * @access	public
	 * @return	object	Mouse
	 */
	static public function getMouse() {
		self::loadMouse();
		return self::$mouse;
	}
}

$mouse = mouseTest::getMouse();
?>