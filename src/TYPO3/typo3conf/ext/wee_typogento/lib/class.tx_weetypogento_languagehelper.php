<?php 

/**
 * TypoGento language helper
 * 
 * 
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

class tx_weetypogento_languageHelper implements t3lib_Singleton {
	
	protected static $_localLanguage = null;
	
	public function getLabel($index, &$arguments = array()) {
		// load the translations
		$this->_initialize();
		// get current language
		$language = $this->_getLanguage();
		// get localized version
		$value = $this->_getLabel($index, $language);
		// set place holder
		return vsprintf($value, $arguments);
	}
	
	protected function _getLabel($index, $language) {
		// check if loaded
		if (!isset(self::$_localLanguage)) {
			return $index;
		}
		// get localized version
		$value = is_string(self::$_localLanguage[$language][$index]) 
			? self::$_localLanguage[$language][$index] : self::$_localLanguage[$language][$index][0]['target'];
		// ...
		if (trim($value) === '') {
			$value = is_string(self::$_localLanguage['default'][$index]) 
				? self::$_localLanguage['default'][$index] : self::$_localLanguage['default'][$index][0]['target'];
				
		}
		if (trim($value) === '') {
			$value = $index;
		}
		// return result
		return $value;
	}
	
	protected function _initialize() {
		// check if init is done
		if (isset(self::$_localLanguage)) {
			return;
		}
		// get current language
		$language = $this->_getLanguage();
		// load the resource
		$resource = t3lib_extMgm::extPath('wee_typogento') . 'locallang.xml';
		self::$_localLanguage = t3lib_div::readLLXMLfile($resource, $language);
	}
	
	protected function _getLanguage() {
		// set default language
		$language = 'default';
		// check if set by typo3
		if (isset( $GLOBALS['LANG']->lang)) {
			$language =  $GLOBALS['LANG']->lang;
		}
		// return result
		return $language;
	}
}
?>