<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Output CLI - Screen output to a Command Line Interface and provides optional logging.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2012 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/

class mouseOutputCli {
	/**
	 * Line Buffer
	 *
	 * @var		array
	 */
	private static $buffer = array();

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
	public function __construct($objectKey = 'cli') {
		$this->objectKey	= $objectKey;
		$this->config		=& mouseHole::$config[$this->objectKey];
	}

	/**
	 * Sends provided string to CLI appended to existing add() buffer.  Automatically sets up line breaks.
	 *
	 * @access	public
	 * @param	string	[Optional] Text to send.
	 * @return	void
	 */
	public function send($text = null) {
		if ($this->config['logging']) {
			//Log Writer Not Yet Implemented
			//$this->log->write($text);
		}

		if (count(self::$buffer)) {
			foreach (self::$buffer as $string) {
				echo $string."\n";
			}
			self::$buffer = array();
		}

		if ($text !== null) {
			echo $text."\n";
		}
	}

	/**
	 * Adds provided text to line buffer.  Calling add() with no text will add a blank line.
	 *
	 * @access	public
	 * @param	string	[Optional] Text to add.
	 * @return	void
	 */
	public function add($text = '') {
		self::$buffer[] = $text;
	}
}
?>