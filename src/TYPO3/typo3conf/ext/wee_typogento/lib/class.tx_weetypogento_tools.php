<?php

/**
 * TypoGento utilities
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_weetypogento_tools {

	/**
	 * The extension configuration array
	 *
	 * @var array
	 */
	protected static $extConfig = null;

	/**
	 * The plugin setup array
	 *
	 * @var array
	 */
	protected static $setup = null;

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
	 * Get the plugin setup
	 *
	 * @return array
	 */
	public static function &getSetup() {
		if (!isset(self::$setup)) {
			$setup = &$GLOBALS['TSFE']->tmpl->setup;
			if (isset($setup['plugin.']['tx_weetypogento_pi1.'])) {
				self::$setup = &$setup['plugin.']['tx_weetypogento_pi1.'];
			} else {
				throw new Exception('No TypoScript template was found');
			}
		}

		return self::$setup;
	}

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
	 * Get the extension configuration array
	 *
	 * @param string $key Key for the property if null it returns the configration array
	 * @throws InvalidArgumentException If a given key was not found
	 * @return mixed
	 */
	public static function &getExtConfig($key = null) {
		if (!isset(self::$extConfig)) {
			self::$extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['wee_typogento']);
		}

		if (isset($key)) {
			if (!isset(self::$extConfig[$key])) {
				throw new InvalidArgumentException(sprintf('Configuration entry \'%s\' was not found', $key));
			}
			return self::$extConfig[$key];
		} else {
			return self::$extConfig;
		}
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
			return tx_weetypogento_tools::getFFvalueFromSheetArray($sheetArray,explode('/', $fieldName), $value);
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
			try{	// create cobj
				return t3lib_div::makeInstance('tslib_cObj');
			} catch(Exception $e) {
				throw new Exception(sprintf('Creating TYPO3 cObj failed: \'%s\'', $e->getMessage()));
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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_tools.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_tools.php']);
}

?>
