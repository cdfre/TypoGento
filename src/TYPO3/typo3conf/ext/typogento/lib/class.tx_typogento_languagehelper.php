<?php 

/**
 * TypoGento language helper
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_languageHelper implements t3lib_Singleton {
	
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
		// get resource path
		$resource = t3lib_extMgm::extPath('typogento') . 'res/language/locallang.xml';
		// load the resource
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4006000) {
			$parser = t3lib_div::makeInstance('t3lib_l10n_parser_Llxml');
			self::$_localLanguage = $parser->getParsedData($resource, $language);
		} else {
			self::$_localLanguage = t3lib_div::readLLXMLfile($resource, $language);
		}
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