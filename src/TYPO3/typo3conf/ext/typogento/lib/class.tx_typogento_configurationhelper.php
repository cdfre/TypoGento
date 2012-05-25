<?php 

/**
 * TypoGento configuration helper
 *
 *
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

class tx_typogento_configurationHelper implements t3lib_Singleton {

	const EXTENSION_MANAGER = 1;
	const TYPOSCRIPT_SETUP = 2;

	protected static $_configuration = array();

	public function __construct() {
	}

	public function getValue($section = self::EXTENSION_MANAGER, $key = '') {
		$section = intval($section);
		$key = strval($key);

		$this->_initializeConfiguration($section);

		if (empty($key)) {
			tx_typogento_div::throwException('lib_configuration_key_not_set_error');
		}
		
		if (!isset(self::$_configuration[$section])) {
			tx_typogento_div::throwException('lib_configuration_section_not_found_error',
				array($section)
			);
		}
		
		if (!isset(self::$_configuration[$section][$key])) {
			tx_typogento_div::throwException('lib_configuration_key_not_found_error',
				array($key)
			);
		}

		return self::$_configuration[$section][$key];
	}
	
	public function getSection($section = self::EXTENSION_MANAGER) {
		$section = intval($section);
	
		$this->_initializeConfiguration($section);
	
		if (!isset(self::$_configuration[$section])) {
			tx_typogento_div::throwException('lib_configuration_section_not_found_error',
				array($section)
			);
		}
	
		return self::$_configuration[$section];
	}

	protected function _initializeConfiguration($section = self::EXTENSION_MANAGER) {
		// init extension config if necessary
		if ($section === self::EXTENSION_MANAGER
				&& !isset(self::$_configuration[$section])) {

			if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typogento'])) {
				tx_typogento_div::throwException('lib_extension_configuration_not_set_error');
			}

			$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typogento']);

			if (!is_array($configuration) || count($configuration) != 6) {
				tx_typogento_div::throwException('lib_extension_configuration_not_valid_error');
			}

			self::$_configuration[$section] = $configuration;
			return;
		}
		// load typoscript setup if necessary
		if ($section === self::TYPOSCRIPT_SETUP
				&& !isset(self::$_configuration[$section])) {
				
			if (!isset($GLOBALS['TSFE']->tmpl->setup)) {
				tx_typogento_div::throwException('lib_typoscript_not_initialized_error');
			}
				
			$setup = &$GLOBALS['TSFE']->tmpl->setup;
				
			if (!isset($setup['plugin.']['tx_typogento_pi1.'])) {
				tx_typogento_div::throwException('lib_typoscript_setup_not_set_error');
			}
				
			self::$_configuration[$section] = $setup['plugin.']['tx_typogento_pi1.'];
			return;
		}
	}
}
?>
