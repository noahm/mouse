<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Output Output - Screen output to HTTP and CLI based interfaces.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2013 NoName Studios
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
	 * Output Line Buffer
	 *
	 * @var		array
	 */
	static private $lineBuffer;

	/**
	 * Output Line Buffer Format
	 *
	 * @var		string
	 */
	private $lineFormat = '%1$s';

	/**
	 * Output Line Buffer Format with Timestamps
	 *
	 * @var		string
	 */
	private $lineFormatWithTimestamp = '[%2$s] %1$s';

	/**
	 * Output Line Timestamp Date Format
	 *
	 * @var		string
	 */
	private $lineDateFormat = 'c';

	/**
	 * Template Folder Locations
	 *
	 * @var		array
	 */
	protected $templateFolders = array();

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
	public function __construct($objectKey = 'http') {
		$this->objectKey	= $objectKey;
		$this->settings		=& mouseHole::$settings[$this->objectKey];
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

		if (count($this->templateFolders)) {
			foreach ($this->templateFolders as $folder) {
				$file = "{$folder}/{$skinClass}.php";
				if (is_file($file)) {
					try {
						include_once($file);
						$this->$skin = new $skinClass;
						return true;
					} catch (Exception $e) {
						return false;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Returns template folders.
	 *
	 * @access	public
	 * @return	array	Current template folders.
	 */
	public function getTemplateFolders() {
		return $this->templateFolders;
	}

	/**
	 * Set Template Folder
	 *
	 * @access	public
	 * @param	string	Complete folder path to where skins are stored for this object instance.
	 * @return	boolean	Success
	 */
	public function addTemplateFolder($folder) {
		if (!$folder) {
			return false;
		}

		$folder = rtrim($folder, '/');
		if (is_dir($folder)) {
			$this->templateFolders[] = $folder;
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
	 * Sends provided string to CLI immediately.  Automatically sets up line breaks.  Calling this function with no parameters will send a blank line.
	 *
	 * @access	public
	 * @param	string	[Optional] Text to send.
	 * @param	integer	[Optional] UTC Timestamp to prepend to the line.
	 * @return	void
	 */
	public function sendLine($text = '', $timestamp = null) {
		echo $this->formatLine($text, $timestamp)."\n";
	}

	/**
	 * Sends provided string to CLI appended to existing addLine() buffer.  Automatically sets up line breaks.
	 *
	 * @access	public
	 * @return	integer	Number of lines sent.
	 */
	public function sendLineBuffer() {
		$totalLines = count(self::$lineBuffer);
		if ($totalLines) {
			foreach (self::$lineBuffer as $string) {
				echo $string."\n";
			}
			self::$lineBuffer = array();
		}
		return $totalLines;
	}

	/**
	 * Adds provided text to line buffer.  Calling add() with no text will add a blank line.
	 *
	 * @access	public
	 * @param	string	[Optional] Text to add.
	 * @param	integer	[Optional] UTC Timestamp to prepend to the line.
	 * @return	void
	 */
	public function addLineToBuffer($text = '', $timestamp = null) {
		self::$lineBuffer[] = $this->formatLine($text, $timestamp);
	}

	/**
	 * Returns all the existing lines.
	 *
	 * @access	public
	 * @return	array	Buffered Lines
	 */
	public function getLineBuffer() {
		return self::$lineBuffer;
	}

	/**
	 * Set the format for lines to display.
	 *
	 * @access	public
	 * @param	string	Line format to be used when not using a timestamp.
	 * @return	void
	 */
	public function setLineFormat($lineFormat) {
		$this->lineFormat = $lineFormat;
	}

	/**
	 * Set the format for lines with timestamps to display.
	 *
	 * @access	public
	 * @param	string	Line format to be used when using a timestamp.
	 * @return	void
	 */
	public function setLineFormatWithTimestamp($lineFormatWithTimestamp) {
		$this->lineFormatWithTimestamp = $lineFormatWithTimestamp;
	}

	/**
	 * Set the format timestamp dates to display.  See date() documentation for valid formats.
	 *
	 * @access	public
	 * @param	string	Timestamp date format.
	 * @return	void
	 */
	public function setLineDateFormat($lineDateFormat) {
		$this->lineDateFormat = $lineDateFormat;
	}

	/**
	 * Set the format for lines to display.
	 *
	 * @access	private
	 * @param	string	Text to add.
	 * @param	integer	UTC Timestamp to prepend to the line.
	 * @return	void
	 */
	private function formatLine($text, $timestamp) {
		return ($timestamp ? sprintf($this->lineFormatWithTimestamp, $text, date($this->lineDateFormat, $timestamp)) : sprintf($this->lineFormat, $text));
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

			if (count($pagination['pages']) > 1) {
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
			}
$HTML .= <<<HTML
	</ul>
HTML;
		return $HTML;
		}
	}
}
?>