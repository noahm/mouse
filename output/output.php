<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Output HTTP - Screen output to a HTTP based interface.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2012 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/

class mouseOutputOutput {
	/**
	 * Output HTML
	 *
	 * @var		string
	 */
	private $content;

	/**
	 * Template Folder Location
	 *
	 * @var		string
	 */
	protected $templateFolder;

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
	public function __construct($objectKey = 'http') {
		$this->objectKey	= $objectKey;
		$this->config		=& mouseHole::$config[$this->objectKey];
	}

	/**
	 * Template Loader
	 *
	 * @access	public
	 * @param	string	Template name after 'skin_'.  To load skin_articles, pass in articles.
	 * @return	boolean	Success
	 */
	public function loadTemplate($skin) {
		if (!$skin) {
			return false;
		} else {
			$skinClass = "skin_{$skin}";
		}

		$file = "{$this->templateFolder}/{$skinClass}.php";
		if (is_file($file)) {
			try {
				include_once($file);
				$this->$skin = new $skinClass;
				return true;
			} catch (Exception $e) {
				return false;
			}
		}
		return false;
	}

	/**
	 * Template Loader
	 *
	 * @access	public
	 * @return	string	Current template folder.
	 */
	public function getTemplateFolder() {
		return $this->templateFolder;
	}

	/**
	 * Set Template Folder
	 *
	 * @access	public
	 * @param	string	Complete folder path to where skins are stored for this object instance.
	 * @return	boolean	Success
	 */
	public function setTemplateFolder($folder) {
		if (!$folder) {
			return false;
		}

		$folder = rtrim($folder, '/');
		if (is_dir($folder)) {
			$this->templateFolder = $folder;
			return true;
		}
		return false;
	}

	/**
	 * Add Output
	 *
	 * @access	public
	 * @param	string	String based output
	 * @return	boolean	Success
	 */
	public function addOutput($output) {
		$this->content .= ($output);
	}

	/**
	 * Get Output
	 *
	 * @access	public
	 * @return	string	HTML Content
	 */
	public function getOutput() {
		return $this->content;
	}

	/**
	 * Send Output
	 *
	 * @access	public
	 * @return	string	HTML Content
	 */
	public function sendOutput() {
		echo $this->content;
	}

	/**
	 * Sends provided string to CLI appended to existing add() buffer.  Automatically sets up line breaks.
	 *
	 * @access	public
	 * @param	string	[Optional] Text to send.
	 * @return	void
	 */
	public function sendLine($text = null) {
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
	public function addLine($text = '') {
		self::$buffer[] = $text;
	}

	/**
	 * Generates page numbers.
	 *
	 * @access	public
	 * @param	integer	Total number of items to be paginated.
	 * @param	integer	How many items to display per page.
	 * @param	integer	Start Position
	 * @param	integer	Number of extra page numbers to show.
	 * @return	array	Generated array of pagination information.
	 */
	public function generatePagination($totalItems, $itemsPerPage = 100, $start = 0, $extraPages = 4) {
		if ($totalItems < 1) {
			return false;
		}

		$currentPage	= floor($start / $itemsPerPage) + 1;
		$totalPages		= ceil($totalItems / $itemsPerPage);
		$lastStart		= floor($totalItems / $itemsPerPage) * $itemsPerPage - $itemsPerPage;

		$pagination['first']	= array('st' => 0, 'selected' => false);
		$pagination['last']	= array('st' => $lastStart, 'selected' => false);
		$pagination['stats']	= array('pages' => $totalPages, 'total' => $totalItems, 'current_page' => $currentPage);

		$start	= min($currentPage, $currentPage - ($extraPages / 2));
		$end	= min($totalPages, $currentPage + ($extraPages / 2));

		if ($start <= 1) {
			$start = 1;
			$end = $start + $extraPages;
		}
		if ($end >= $totalPages) {
			$end = $totalPages;
			$start = max($end - $extraPages, ($currentPage - ($extraPages / 2)) - (($extraPages / 2) - ($end - $currentPage)));
		}

		for ($i = $start; $i <= $end; $i++) {
			$pagination['pages'][$i] = array('st' => ($i * $itemsPerPage) - $itemsPerPage, 'selected' => ($i == $currentPage ? true : false));
		}

		return $pagination;
	}

	/**
	 * Generates pagination template.
	 *
	 * @access	public
	 * @param	array	Array of pagination information.
	 */
	public function paginationTemplate($pagination) {
		if (count($pagination['pages'])) {
$HTML .= <<<HTML
	<ul class='pagination'>
HTML;
			if ($pagination['stats']) {
				$HTML .= "<li class='pagination_stats'>Page {$pagination['stats']['current_page']} of {$pagination['stats']['pages']}</li>";
			}
			if ($pagination['first']) {
				$HTML .= "<li><a href='?st={$pagination['first']['st']}'>&laquo;</a></li>";
			}
			foreach ($pagination['pages'] as $page => $info) {
				if ($page > 0) {
					$HTML .= "<li".($info['selected'] ? " class='selected'" : null)."><a href='?st={$info['st']}'>{$page}</a></li>";
				}
			}
			if ($pagination['last']) {
				$HTML .= "<li><a href='?st={$pagination['last']['st']}'>&raquo;</a></li>";
			}
$HTML .= <<<HTML
	</ul>
HTML;
		return $HTML;
		}
	}
}
?>