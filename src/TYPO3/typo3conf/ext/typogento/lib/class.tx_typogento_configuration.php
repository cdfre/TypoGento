<?php 

/**
 * Configuration helper
 *
 * Encapsulate access to the configuration.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_configuration implements t3lib_Singleton {

	/**
	 * In $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typogento']
	 * @var int
	 */
	const EXTENSION = 0;
	
	/**
	 * In $this->_frontend->config['config']['tx_typogento.']
	 * 
	 * @var int
	 */
	const PAGE = 1;
	
	/**
	 * In $this->_frontend->tmpl->setup['plugin.']['tx_typogento_pi1.]
	 * 
	 * @var int
	 */
	const PLUGIN = 2;
	
	/**
	 * In $this->_frontend->config['tx_typogento_cache']
	 * 
	 * @var int
	 */
	const CACHE = 3;
	
	/**
	 * In $this->_frontend->config['config']
	 *
	 * @var int
	 */
	const SYSTEM = 4;

	/**
	 * @var array
	 */
	protected $_sections = array();
	
	/**
	 * @var array
	 */
	protected $_merges = array();
	
	/**
	 * @var tslib_fe
	 */
	protected $_frontend = null;

	/**
	 * Constructor
	 * 
	 * @param tslib_fe $frontend The page frontend
	 */
	public function __construct(tslib_fe $frontend = null) {
		if ($frontend == null) {
			$this->_frontend = $GLOBALS['TSFE'];
		} else {
			$this->_frontend = $frontend;
		}
	}
	
	public function has($path, $section = self::PAGE) {
		$section = (int)$section;
		$path = (string)$path;
		$default = null;
	
		$this->_initialize($section);
	
		$value = &$this->_read($path, $default, $section);
	
		return isset($value);
	}

	public function &get($path, $default = null, $section = self::PAGE) {
		$section = (int)$section;
		$path = (string)$path;

		$this->_initialize($section);
		
		$value = &$this->_read($path, $default, $section);
		
		return $value;
	}
	
	public function &set($path, $value, $section = self::PAGE) {
		$section = (int)$section;
		$path = (string)$path;
	
		$this->_initialize($section);
	
		$this->_write($path, $value, $section);
	}
	
	public function merge(array &$entries, $section = self::PAGE) {
		$section = (int)$section;
		
		$this->_initialize($section);
		
		$next = count($this->_sections) + 5;
		
		$this->_sections[$next] = t3lib_div::array_merge_recursive_overrule(
			$this->_sections[$section], $entries
		);
		
		$this->_merges[$next] = $section;
		
		return $next;
	}
	
	protected function &_read($path, &$default, $section) {
		
		$entries = &$this->_sections[$section];
		
		if ($section > 5) {
			$section = $this->_merges[$section];
		}

		switch ($section) {
			case self::PAGE:
			case self::PLUGIN:
			case self::CACHE:
			case self::SYSTEM:
				$default = &tx_typogento_div::getTypoScriptValue(
					$entries, $path, $default
				);
				break;
			case self::EXTENSION:
				$default = &tx_typogento_div::getArrayValue(
					$entries, $path, $default
				);
				break;
		}
		
		return $default;
	}
	
	protected function &_write($path, &$value, $section) {
	
		$entries = &$this->_sections[$section];
	
		if ($section > 5) {
			$section = $this->_merges[$section];
		}
	
		switch ($section) {
			case self::PAGE:
			case self::PLUGIN:
			case self::CACHE:
			case self::SYSTEM:
				tx_typogento_div::setTypoScriptValue(
					$entries, $path, $value
				);
				break;
			case self::EXTENSION:
				tx_typogento_div::setArrayValue(
					$entries, $path, $value
				);
				break;
		}
	}

	protected function _initialize($section = self::PAGE) {
		// skip
		if (isset($this->_sections[$section])) {
			return;
		}
		// initialize
		switch ($section) {
			case self::EXTENSION:
				if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typogento'])) {
					throw tx_typogento_div::exception('lib_extension_configuration_not_set_error');
				}
			
				$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typogento']);
			
				if (!is_array($configuration) || count($configuration) != 6) {
					throw tx_typogento_div::exception('lib_extension_configuration_not_valid_error');
				}
			
				$this->_sections[$section] = &$configuration;
				break;
			case self::PAGE:
				if (!isset($this->_frontend->config)) {
					throw tx_typogento_div::exception('lib_unknown_error');
				}
				
				if (!isset($this->_frontend->config['config']['tx_typogento.'])) {
					throw tx_typogento_div::exception('lib_typoscript_setup_not_set_error');
				}
			
				$this->_sections[$section] = &$this->_frontend->config['config']['tx_typogento.'];
				break;
			case self::PLUGIN:
				if (!isset($this->_frontend->tmpl->setup)) {
					throw tx_typogento_div::exception('lib_typoscript_not_initialized_error');
				}
			
				if (!isset($this->_frontend->tmpl->setup['plugin.']['tx_typogento_pi1.'])) {
					throw tx_typogento_div::exception('lib_typoscript_setup_not_set_error');
				}
			
				$this->_sections[$section] = &$this->_frontend->tmpl->setup['plugin.']['tx_typogento_pi1.'];
				break;
			case self::CACHE:
				if (!isset($this->_frontend->config)) {
					throw tx_typogento_div::exception('lib_unknown_error');
				}
				
				if (!isset($this->_frontend->config['tx_typogento_cache.'])) {
					$this->_frontend->config['tx_typogento_cache.'] = array();
				}
					
				$this->_sections[$section] = &$this->_frontend->config['tx_typogento_cache.'];
				break;
			case self::SYSTEM:
					if (!isset($this->_frontend->config)) {
						throw tx_typogento_div::exception('lib_unknown_error');
					}
				
					if (!isset($this->_frontend->config['config'])) {
						throw tx_typogento_div::exception('lib_typoscript_setup_not_set_error');
					}
						
					$this->_sections[$section] = &$this->_frontend->config['config'];
					break;
		}
		// vallidate
		if (!isset($this->_sections[$section])) {
			throw tx_typogento_div::exception('lib_configuration_section_not_found_error',
				array($section)
			);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_configuration.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_configuration.php']);
}

?>
