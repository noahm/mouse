<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Config File - Native Mouse configuration from a file.
 *
 * @author 		Alexia E. Smith
 * @license		GNU General Public License v3
 * @package		Mouse Framework
 * @link		https://github.com/Alexia/mouse
 *
**/
namespace mouse\config;
use mouse;

class file {
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
	 * @param	[Optional] Object key used to initialize the object to mouse.  Also servers as the configuration array key.
	 * @return	void
	 */
	public function __construct($objectKey = 'config') {
		$this->objectKey	= $objectKey;

	}
}
?>