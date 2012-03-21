<?php

/**
 * TypoGento utilities
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_weetypogento_div {

	/**
	 * The template configuration array
	 *
	 * @var array
	 */
	protected static $config = null;

	/**
	 *
	 * Enter description here ...
	 * @var unknown_type
	 */
	protected static $cObj = null;

	/**
	 * Get the template configuration array
	 *
	 * @return array
	 */
	public static function &getConfig() {
		if (!isset(self::$config)) {
			self::$config = &$GLOBALS['TSFE']->config['config'];
		}

		return self::$config;
	}

	/**
	 * Return value from somewhere inside a FlexForm structure
	 *
	 * @param	array		FlexForm data
	 * @param	string		Field name to extract. Can be given like "test/el/2/test/el/field_templateObject" where each part will dig a level deeper in the FlexForm data.
	 * @param	string		Sheet pointer, eg. "sDEF"
	 * @param	string		Language pointer, eg. "lDEF"
	 * @param	string		Value pointer, eg. "vDEF"
	 * @return	string		The content.
	 */
	public static function &getFFvalue($T3FlexForm_array, $fieldName, $sheet = 'sDEF', $lang = 'lDEF', $value = 'vDEF') {
		$sheetArray = is_array($T3FlexForm_array) ? $T3FlexForm_array['data'][$sheet][$lang] : '';
		if (is_array($sheetArray)) {
			return tx_weetypogento_div::getFFvalueFromSheetArray($sheetArray,explode('/', $fieldName), $value);
		}
	}

	/**
	 * Returns part of $sheetArray pointed to by the keys in $fieldNameArray
	 *
	 * @param	array		Multidimensiona array, typically FlexForm contents
	 * @param	array		Array where each value points to a key in the FlexForms content - the input array will have the value returned pointed to by these keys. All integer keys will not take their integer counterparts, but rather traverse the current position in the array an return element number X (whether this is right behavior is not settled yet...)
	 * @param	string		Value for outermost key, typ. "vDEF" depending on language.
	 * @return	mixed		The value, typ. string.
	 * @access private
	 * @see pi_getFFvalue()
	 */
	public static function &getFFvalueFromSheetArray($sheetArray, $fieldNameArr, $value) {
		$tempArr=$sheetArray;
		foreach($fieldNameArr as $k => $v) {
			if (t3lib_div::testInt($v)) {
				if (is_array($tempArr)) {
					$c=0;
					foreach($tempArr as $values) {
						if ($c==$v) {
							#debug($values);
							$tempArr=$values;
						break;
						}
						$c++;
					}
				}
			} else {
				$tempArr = $tempArr[$v];
			}
		}
		return $tempArr[$value];
	}

	/**
	 * Return TYPO3 cObj reference
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
				return t3lib_div::makeInstance('tslib_cObj');
			} catch(Exception $e) {
				tx_weetypogento_div::throwException('lib_initializing_content_object_failed_error', 
					array($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']), $e
				);
			}
		}
	}

	/**
	 * Get store code for current frontend language
	 *
	 * @return string
	 */
	public static function getFELangStoreCode() {

		if (empty($GLOBALS['TSFE']->config['config']['sys_language_uid'])) {
			if ($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_weetypogento_pi1.']['storeName']) {
				return $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_weetypogento_pi1.']['storeName'];
			}
			return 'default';
		}

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'tx_weetypogento_store', 'sys_language', sprintf('uid = %d', $GLOBALS['TSFE']->config['config']['sys_language_uid'])
		);

		$res = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		if (!($store = $res['tx_weetypogento_store'])) {
			if ($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_weetypogento_pi1.']['storeName']) {
				$store = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_weetypogento_pi1.']['storeName'];
			} else {
				$store = 'default';
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $store;
	}
	
	public static function exception($message, $arguments = array(), Exception $previous = null) {
		// get translation helper
		$helper = t3lib_div::makeInstance('tx_weetypogento_languageHelper');
		// check previous error message if set
		if (isset($previous)) {
			// get previous error message
			$previousMessage = $previous->getMessage();
			// replace message if empty
			if (empty($previousMessage)) {
				$previousMessage = $helper->getLabel('lib_unknown_error', $arguments);
			}
			// add previous message to the args
			$arguments[] = $previousMessage;
			//die(var_dump($previousMessage));
		}
		// get message translation
		$message = $helper->getLabel($message, $arguments);
		// throw the exception
		return new Exception($message, 0, $previous);
	}
	
	public static function throwException($message, $arguments = array(), Exception $previous = null) {
		throw self::exception($message, $arguments, $previous);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_div.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_div.php']);
}

?>
