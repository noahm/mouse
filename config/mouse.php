<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Config Mouse - Native Mouse Configuration
 *
 * @author 		Alexia E. Smith
 * @license		GNU General Public License v3
 * @package		Mouse Framework
 * @link		https://github.com/Alexia/mouse
 *
**/
namespace mouse\config;
use mouse;

class mouse {
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
	public function __construct($objectKey = 'config') {
		$this->objectKey	= $objectKey;

	}
}
?>