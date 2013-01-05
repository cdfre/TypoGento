<?php

namespace Tx\Typogento\Utility;
/**
 * Utilities
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class GeneralUtility {
	
	/**
	 * Get flat version of an associative array
	 * 
	 * @param array $array The array to flatten
	 * @param unknown_type $prefix The prefix for each entry
	 * 
	 * @return array The flat array
	 */
	public static function &getFlatArray(array &$array, $prefix = '') {
		// helper
		$result = array();
		$i = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));
		// iterate array recursive
		foreach ($i as $key => $value) {
			// build path
			for ($j = 0; $j < $i->getDepth(); $j++) {
				$key = $i->getSubIterator($j)->key() . '.' . $key;
			}
			// add result
			$result[$prefix . $key] = $value;
		}
		// return result
		return $result;
	}
	
	/**
	 * Get value of a TypoScript array by using "root.branch.leaf" notation
	 *
	 * @param array $typoscript TypoScript array to traverse
	 * @param string $path Path to a specific option to extract
	 * @param mixed $default Value to use if the path was not found
	 *
	 * @return mixed
	 */
	public static function &getTypoScriptValue(array &$array, $path, $default = null) {
		// prepare path
		$path = str_replace('.', '.#', $path);
		$array = &self::getArrayValue($array, $path, $default, '#');
		return $array;
	}
	
	/**
	 * Set value of a TypoScript array by using "root.branch.leaf" notation
	 * 
	 * @param array $typoscript TypoScript array to traverse
	 * @param string $path Path to a specific option to change
	 * @param mixed &$value Value to set
	 */
	public static function setTypoScriptValue(array &$array, $path, $value) {
		// prepare path
		$path = str_replace('.', '.#', $path);
		self::setArrayValue($array, $path, $value, '#');
	}
	
	/**
	 * Get value of an array by using "root.branch.leaf" notation
	 *
	 * @param array $array Array to traverse
	 * @param string $path Path to a specific option to extract
	 * @param mixed $default Value to use if the path was not found
	 * 
	 * @return mixed
	 */
	public static function &getArrayValue(array &$array, $path, $default = null, $delimiter = '.') {
		// prepare path
		$path = rtrim($path, $delimiter);
		$keys = explode($delimiter, $path);
		// iterate parts
		while(count($keys) > 1) {
			// next key
			$key = array_shift($keys);
			// check key
			if (!isset($array[$key]) 
			|| !is_array($array[$key])) {
				return $default;
			}
			// next value
			$array = &$array[$key];
		}
		// return value
		$key = reset($keys);
		return (isset($array[$key]) ? $array[$key] : $default);
	}
	
	/**
	 * Set value of an array by using "root.branch.leaf" notation
	 *
	 * @param array $array Array to traverse
	 * @param string $path Path to a specific option to extract
	 * @param mixed $default Value to use if the path was not found
	 *
	 * @return mixed
	 */
	public static function setArrayValue(array &$array, $path, &$value, $delimiter = '.') {
		// prepare path
		$path = rtrim($path, $delimiter);
		$keys = explode($delimiter, $path);
		// loop through each part and extract its value
		while(count($keys) > 1) {
			// next key
			$key = array_shift($keys);
			// check key
			if (!isset($array[$key])) {
				$array[$key] = array();
			} else if (!is_array($array[$key])) {
				throw new Exception(sprintf('The path "%s" is not valid.', $path), 1356930770);
			}
			// next value
			$array = &$array[$key];
		}
		// set value
		$key = reset($keys);
		$array[$key] = $value;
	}

	/**
	 * Return value from somewhere inside a FlexForm structure
	 *
	 * @param array FlexForm array
	 * @param string Field name to extract, e.g. "test.el.field_templateObject" where each part will dig a level deeper in the FlexForm data.
	 * @param string Sheet pointer, e.g. "sDEF"
	 * @param string Language pointer, e.g. "lDEF"
	 * @param string Value pointer, e.g. "vDEF"
	 * 
	 * @return string The content.
	 */
	public static function &getFlexFormValue(array &$array, $path, $sheet = 'sDEF', $language = 'lDEF', $value = 'vDEF', $default = null) {
		$array = &$array['data'][$sheet][$language];
		
		if (!is_array($array)) {
			return $default;
		}
		
		$keys = explode('/', $path);
		
		foreach($keys as $k => $v) {
			if (\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($v)) {
				if (is_array($array)) {
					$c = 0;
					foreach($array as $item) {
						if ($c == $v) {
							$array = &$item;
							break;
						}
						$c++;
					}
				}
			} else {
				$array = &$array[$v];
			}
		}
		
		return $array[$value];
	}
	
	/**
	 * Get FlexForm array from content object
	 * 
	 * @param tslib_fe $frontend The page frontend
	 * @param unknown_type $type List type of the content object
	 * @param unknown_type $column Page column of the content object
	 * @param unknown_type $position Position of the content object
	 */
	public static function &getContentFlexForm(\TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $frontend, $type, $column = 0, $position = 0) {
		// select
		$where = 'pid=\'' . $frontend->id . '\' AND colPos = ' . $column . ' AND list_type=\'' . $type . '\' ';
		$where .= $frontend->sys_page->enableFields('tt_content');
		$limit = $position . ',1';
		$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'pi_flexform', 'tt_content', $where, '', 'sorting', $limit
		);
		// validate
		if (!isset($row[0]['pi_flexform'])) {
			// result
			return null;
		}
		// flexform
		$flexform = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($row[0]['pi_flexform']);
		// result
		return $flexform;
	}

	/**
	 * Return a content object
	 *
	 * @return tslib_cObj
	 */
	public static function getContentObject() {
		// check if typo3 cobj is set
		if (isset($GLOBALS['TSFE']->cObj) && $GLOBALS['TSFE']->cObj instanceof tslib_cObj) {
			// get tsfe cobj
			return $GLOBALS['TSFE']->cObj;
				
		} else {
			try{
				// create cobj
				return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
			} catch(Exception $e) {
				throw new Exception(sprintf('The content object renderer could not be created for the requested URL "%s": %s', $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'], $e->getMessage()), 1356930991, $e);
			}
		}
	}

	/**
	 * Get store code for current frontend language
	 * 
	 * @todo Refactor
	 * @return string
	 */
	public static function getFELangStoreCode() {

		if (empty($GLOBALS['TSFE']->config['config']['sys_language_uid'])) {
			if ($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_typogento_pi1.']['storeName']) {
				return $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_typogento_pi1.']['storeName'];
			}
			return 'default';
		}

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_typogento_store', 'sys_language', sprintf('uid = %d', $GLOBALS['TSFE']->config['config']['sys_language_uid'])
		);

		$res = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		if (!($store = $res['tx_typogento_store'])) {
			if ($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_typogento_pi1.']['storeName']) {
				$store = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_typogento_pi1.']['storeName'];
			} else {
				$store = 'default';
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $store;
	}
}
?>
