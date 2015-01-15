<?php
/**
 * NoName Studios
 * Mouse Framework
 * Mouse Utility Array - Array functions not included in PHP Core.
 *
 * @author 		Alexia E. Smith
 * @license		GNU General Public License v3
 * @package		Mouse Framework
 * @link		https://github.com/Alexia/mouse
 *
**/
namespace mouse\utility;
use mouse;

class array {
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
	public function __construct($objectKey = 'array') {
		$this->objectKey	= $objectKey;
		$this->settings		=& mouse\hole::$settings[$this->objectKey];
	}

	/**
	 * Sort a one deep multidimensional array by the values of a specified key.
	 *
	 * @access	public
	 * @param	array	Array to sort
	 * @param	string	Subarray key to sort by.
	 * @param	string	[Optional] Sorting method to use, default of natcasesort() or optionally asort().
	 * @return	array	Sorted Array
	 */
	public function sortByKeyValue($array = [], $sortKey, $sortOption = 'natcasesort') {
		if (!is_array($array)) {
			return false;
		}

		$sorter = [];
		foreach ($array as $key => $info) {
			$sorter[$key] = $info[$sortKey];
		}

		if ($sortOption == 'asort') {
			asort($sorter);
		} else {
			natcasesort($sorter);
		}

		$sortedArray = [];
		foreach ($sorter as $key => $value) {
			$sortedArray[$key] = $array[$key];
		}
		return $sortedArray;
	}

	/**
	 * Sort a one deep multidimensional array by the values of a specified key.
	 * Example: Searching for $searchTerm of 'sedan' and a $searchKey array of ['type', 'model'] would return one result from this array.
	 * 		$array = [
	 * 			['type' => 'car', 'model' => 'van'],
	 * 			['type' => 'car', 'model' => 'sedan']
	 * 		];
	 *
	 * @access	public
	 * @param	array	Array to search
	 * @param	array	Subarray keys to search by.  This can be a one dimensional array of keys to check against for the search term.
	 * @param	string	Value to search for.
	 * @return	mixed	Array of search results.  Returns false for invalid paramters or no results found.
	 */
	public function searchByKeyValue($array = [], $searchKeys = [], $searchTerm = '') {
		if (!is_array($array) || !is_array($searchKeys)) {
			return false;
		}

		$searchTerm = mb_strtolower($searchTerm, 'UTF-8');
		$found = false;

		foreach ($array as $key => $info) {
			foreach ($searchKeys as $sKey) {
				if (is_array($info[$sKey])) {
					$_temp = mb_strtolower(implode(',', $info[$sKey]), 'UTF-8');
				} else {
					$_temp = mb_strtolower($info[$sKey], 'UTF-8');
				}
				if (strpos($_temp, $searchTerm) !== false) {
					$found[$key] = $info;
				}
			}
		}

		return $found;
	}
}
?>