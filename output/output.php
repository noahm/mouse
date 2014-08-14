<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Output Output - Screen output to HTTP and CLI based interfaces.
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2010 - 2014 NoName Studios
 * @license		All Rights Reserved
 * @package		Mouse Framework
 * @link		http://www.nonamestudios.com/
 * @version		2.0
 *
**/
namespace mouse\Output;
use mouse;

class Output {
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
	 * Ignore calls to sendLine and sendLineBuffer when not on the CLI
	 *
	 * @var		boolean
	 */
	protected $cliOutputOnly = true;

	/**
	 * Template Folder Locations
	 *
	 * @var		array
	 */
	protected $templateFolders = [];

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
		$this->settings		=& mouse\Hole::$settings[$this->objectKey];
	}

	/**
	 * Gets whether calls to sendLine and sendLineBuffer will be ignored
	 *
	 * @access	public
	 * @return	boolean	Current template folders.
	 */
	public function getCliOnly() {
		return $this->cliOutputOnly;
	}

	/**
	 * Sets whether calls to sendLine and sendLineBuffer will be ignored
	 *
	 * @access	public
	 * @param	boolean
	 * @return	bool	the previous value
	 */
	public function setCliOnly($bool = true) {
		$prev = $this->cliOutputOnly;
		$this->cliOutputOnly = !!$bool;
		return $prev;
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
	 * Sends provided string to CLI immediately.  Automatically sets up line breaks.
	 * Calling this function with no parameters will send a blank line.
	 *
	 * @access	public
	 * @param	string	[Optional] Text to send.
	 * @param	integer	[Optional] UTC Timestamp to prepend to the line.
	 * @return	boolean	Successfully sent, false if we are not on the CLI.
	 */
	public function sendLine($text = '', $timestamp = null) {
		if ($this->cliOutputOnly && PHP_SAPI != 'cli') {
			return false;
		}

		echo $this->formatLine($text, $timestamp)."\n";

		return true;
	}

	/**
	 * Sends provided string to CLI appended to existing addLine() buffer.  Automatically sets up line breaks.
	 *
	 * @access	public
	 * @return	mixed	Integer number of lines sent or false if we are not on the CLI.
	 */
	public function sendLineBuffer() {
		if ($this->cliOutputOnly && PHP_SAPI != 'cli') {
			return false;
		}
		$totalLines = count(self::$lineBuffer);
		if ($totalLines) {
			foreach (self::$lineBuffer as $string) {
				echo $string."\n";
			}
			self::$lineBuffer = [];
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
	 * Formats a lines for display according to the configured formats.
	 *
	 * @access	private
	 * @param	string	Text to add.
	 * @param	integer	UTC Timestamp to prepend to the line.
	 * @return	string
	 */
	private function formatLine($text, $timestamp) {
		return ($timestamp ? sprintf($this->lineFormatWithTimestamp, $text, date($this->lineDateFormat, $timestamp)) : sprintf($this->lineFormat, $text));
	}

	/**
	 * Sends a completed HTTP status based on the basic number passed.
	 *
	 * @access	public
	 * @param	integer	HTTP/1.1 Numeric Status Code
	 * @return	mixed	Valid codes outputs the status code to headers and returns the header string.  Invalid codes return false.
	 */
	public function sendHTTPStatus($code) {
		switch ($code) {
			case 100:
				$statusText = 'Continue';
				break;
			case 101:
				$statusText = 'Switching Protocols';
				break;
			case 102:
				$statusText = 'Processing';
				break;
			case 200:
				$statusText = 'OK';
				break;
			case 201:
				$statusText = 'Created';
				break;
			case 202:
				$statusText = 'Accepted';
				break;
			case 203:
				$statusText = 'Non-Authoritative Information';
				break;
			case 204:
				$statusText = 'No Content';
				break;
			case 205:
				$statusText = 'Reset Content';
				break;
			case 206:
				$statusText = 'Partial Content';
				break;
			case 207:
				$statusText = 'Multi-Status';
				break;
			case 208:
				$statusText = 'Already Reported';
				break;
			case 226:
				$statusText = 'IM Used';
				break;
			case 300:
				$statusText = 'Multiple Choices';
				break;
			case 301:
				$statusText = 'Moved Permanently';
				break;
			case 302:
				$statusText = 'Found';
				break;
			case 303:
				$statusText = 'See Other';
				break;
			case 304:
				$statusText = 'Not Modified';
				break;
			case 305:
				$statusText = 'Use Proxy';
				break;
			case 306:
				$statusText = 'Reserved';
				break;
			case 307:
				$statusText = 'Temporary Redirect';
				break;
			case 308:
				$statusText = 'Permanent Redirect';
				break;
			case 400:
				$statusText = 'Bad Request';
				break;
			case 401:
				$statusText = 'Unauthorized';
				break;
			case 402:
				$statusText = 'Payment Required';
				break;
			case 403:
				$statusText = 'Forbidden';
				break;
			case 404:
				$statusText = 'Not Found';
				break;
			case 405:
				$statusText = 'Method Not Allowed';
				break;
			case 406:
				$statusText = 'Not Acceptable';
				break;
			case 407:
				$statusText = 'Proxy Authentication Required';
				break;
			case 408:
				$statusText = 'Request Timeout';
				break;
			case 409:
				$statusText = 'Conflict';
				break;
			case 410:
				$statusText = 'Gone';
				break;
			case 411:
				$statusText = 'Length Required';
				break;
			case 412:
				$statusText = 'Precondition Failed';
				break;
			case 413:
				$statusText = 'Request Entity Too Large';
				break;
			case 414:
				$statusText = 'Request-URI Too Long';
				break;
			case 415:
				$statusText = 'Unsupported Media Type';
				break;
			case 416:
				$statusText = 'Requested Range Not Satisfiable';
				break;
			case 417:
				$statusText = 'Expectation Failed';
				break;
			case 422:
				$statusText = 'Unprocessable Entity';
				break;
			case 423:
				$statusText = 'Locked';
				break;
			case 424:
				$statusText = 'Failed Dependency';
				break;
			case 426:
				$statusText = 'Upgrade Required';
				break;
			case 428:
				$statusText = 'Precondition Required';
				break;
			case 429:
				$statusText = 'Too Many Requests';
				break;
			case 431:
				$statusText = 'Request Header Fields Too Large';
				break;
			case 500:
				$statusText = 'Internal Server Error';
				break;
			case 501:
				$statusText = 'Not Implemented';
				break;
			case 502:
				$statusText = 'Bad Gateway';
				break;
			case 503:
				$statusText = 'Service Unavailable';
				break;
			case 504:
				$statusText = 'Gateway Timeout';
				break;
			case 505:
				$statusText = 'HTTP Version Not Supported';
				break;
			case 506:
				$statusText = 'Variant Also Negotiates (Experimental)';
				break;
			case 507:
				$statusText = 'Insufficient Storage';
				break;
			case 508:
				$statusText = 'Loop Detected';
				break;
			case 510:
				$statusText = 'Not Extended';
				break;
			case 511:
				$statusText = 'Network Authentication Required';
				break;
			default:
				return false;
				break;
		}

		$header = 'HTTP/1.1 '.$code.' '.$statusText;

		header($header);

		return $header;
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
	public function generatePagination($totalItems, $itemsPerPage = 100, $itemStart = 0, $extraPages = 4) {
		if ($totalItems < 1) {
			return false;
		}

		$currentPage	= floor($itemStart / $itemsPerPage) + 1;
		$totalPages		= ceil($totalItems / $itemsPerPage);
		$lastStart		= floor($totalItems / $itemsPerPage) * $itemsPerPage;

		$pagination['first']	= ['st' => 0, 'selected' => false];
		$pagination['last']		= ['st' => $lastStart, 'selected' => false];
		$pagination['stats']	= ['pages' => $totalPages, 'total' => $totalItems, 'current_page' => $currentPage];

		$pageStart	= min($currentPage, $currentPage - ($extraPages / 2));
		$pageEnd	= min($totalPages, $currentPage + ($extraPages / 2));

		if ($pageStart <= 1) {
			$pageStart = 1;
			$pageEnd = $pageStart + $extraPages;
		}
		if ($pageEnd >= $totalPages) {
			$pageEnd = $totalPages;
			$pageStart = max($pageEnd - $extraPages, ($currentPage - ($extraPages / 2)) - (($extraPages / 2) - ($pageEnd - $currentPage)));
		}

		for ($i = $pageStart; $i <= $pageEnd; $i++) {
			if ($i > 0) {
				$pagination['pages'][$i] = ['st' => ($i * $itemsPerPage) - $itemsPerPage, 'selected' => ($i == $currentPage ? true : false)];
			}
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