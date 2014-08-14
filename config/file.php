<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Config File - Native Mouse configuration from a file.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2014 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		https://bitbucket.org/ashlawnk/mouse
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