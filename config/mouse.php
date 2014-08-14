<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Config Mouse - Native Mouse Configuration
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2014 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/
namespace mouse\Config;
use mouse;

class Mouse {
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
	 * @param	[Optional] Object key used to initialize the object to mouse.  Also serves as the settings array key.
	 * @return	void
	 */
	public function __construct($objectKey = 'config') {
		$this->objectKey	= $objectKey;

	}
}
?>