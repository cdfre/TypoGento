<?php 

namespace Tx\Typogento\Configuration;

use \Tx\Typogento\Utility\GeneralUtility;

/**
 * Configuration Manager
 *
 * Encapsulate access to the different configuration sections.
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class ConfigurationManager implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * In $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typogento']
	 * 
	 * @var int
	 */
	const EXTENSION = 0;
	
	/**
	 * In \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::config['config']['tx_typogento.']
	 * 
	 * @var int
	 */
	const PAGE = 1;
	
	/**
	 * In \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::tmpl::setup['plugin.']['tx_typogento_pi1.][settings.]
	 * 
	 * @var int
	 */
	const PLUGIN = 2;
	
	/**
	 * In \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::config['tx_typogento_cache']
	 * 
	 * @var int
	 */
	const CACHE = 3;
	
	/**
	 * In \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::config['config']
	 *
	 * @var int
	 */
	const SYSTEM = 4;

	/**
	 * @var array
	 */
	protected $sections = array();
	
	/**
	 * @var array
	 */
	protected $merges = array();
	
	/**
	 * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
	 */
	protected $frontend = null;

	/**
	 * Constructor
	 * 
	 * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $frontend The page frontend
	 */
	public function __construct(\TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $frontend = null) {
		if ($frontend == null) {
			$this->frontend = $GLOBALS['TSFE'];
		} else {
			$this->frontend = $frontend;
		}
	}
	
	public function has($path, $section = self::PAGE) {
		$section = (int)$section;
		$path = (string)$path;
		$default = null;
	
		$this->initialize($section);
	
		$value = &$this->read($path, $default, $section);
	
		return isset($value);
	}

	public function &get($path, $default = null, $section = self::PAGE) {
		$section = (int)$section;
		$path = (string)$path;

		$this->initialize($section);
		
		$value = &$this->read($path, $default, $section);
		
		return $value;
	}
	
	public function &set($path, $value, $section = self::PAGE) {
		$section = (int)$section;
		$path = (string)$path;
	
		$this->initialize($section);
	
		$this->write($path, $value, $section);
	}
	
	public function merge(array &$entries, $section = self::PAGE) {
		$section = (int)$section;
		
		$this->initialize($section);
		
		$next = count($this->sections) + 5;
		
		$this->sections[$next] = \TYPO3\CMS\Core\Utility\GeneralUtility::array_merge_recursive_overrule(
			$this->sections[$section], $entries
		);
		
		$this->merges[$next] = $section;
		
		return $next;
	}
	
	protected function &read($path, &$default, $section) {
		
		$entries = &$this->sections[$section];
		
		if ($section > 5) {
			$section = $this->merges[$section];
		}

		switch ($section) {
			case self::PAGE:
			case self::PLUGIN:
			case self::CACHE:
			case self::SYSTEM:
				$default = &GeneralUtility::getTypoScriptValue(
					$entries, $path, $default
				);
				break;
			case self::EXTENSION:
				$default = &GeneralUtility::getArrayValue(
					$entries, $path, $default
				);
				break;
		}
		
		return $default;
	}
	
	protected function &write($path, &$value, $section) {
	
		$entries = &$this->sections[$section];
	
		if ($section > 5) {
			$section = $this->merges[$section];
		}
	
		switch ($section) {
			case self::PAGE:
			case self::PLUGIN:
			case self::CACHE:
			case self::SYSTEM:
				GeneralUtility::setTypoScriptValue(
					$entries, $path, $value
				);
				break;
			case self::EXTENSION:
				GeneralUtility::setArrayValue(
					$entries, $path, $value
				);
				break;
		}
	}

	protected function initialize($section = self::PAGE) {
		// skip
		if (isset($this->sections[$section])) {
			return;
		}
		// initialize
		switch ($section) {
			case self::EXTENSION:
				if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typogento'])) {
					throw new Exception('Missing the extension configuration.', 1356837524);
				}
			
				$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typogento']);
			
				if (!is_array($configuration) || count($configuration) != 6) {
					throw new Exception('The extension configuration is damaged.', 1356837459);
				}
			
				$this->sections[$section] = &$configuration;
				break;
			case self::PAGE:
				if (!isset($this->frontend->config)) {
					throw new Exception('Unknown error.', 1356837083);
				}
				
				if (!isset($this->frontend->config['config']['tx_typogento.'])) {
					throw new Exception('Missing the page related configuration (config.tx_typogento) in the TypoScript frontend setup.', 1356837422);
				}
			
				$this->sections[$section] = &$this->frontend->config['config']['tx_typogento.'];
				break;
			case self::PLUGIN:
				if (!isset($this->frontend->tmpl->setup)) {
					throw new Exception('The TypoScript frontend setup is not initialized.', 1356837053);
				}
			
				if (!isset($this->frontend->tmpl->setup['plugin.']['tx_typogento.']['settings.'])) {
					throw new Exception('Missing the plugin related configuration (plugin.tx_typogento.settings) in the TypoScript frontend setup.', 1356837022);
				}
			
				$this->sections[$section] = &$this->frontend->tmpl->setup['plugin.']['tx_typogento.']['settings.'];
				break;
			case self::CACHE:
				if (!isset($this->frontend->config)) {
					throw new Exception('Unknown error.', 1356836494);
				}
				
				if (!isset($this->frontend->config['tx_typogento_cache.'])) {
					$this->frontend->config['tx_typogento_cache.'] = array();
				}
					
				$this->sections[$section] = &$this->frontend->config['tx_typogento_cache.'];
				break;
			case self::SYSTEM:
					if (!isset($this->frontend->config)) {
						throw new Exception('Unknown error.', 1356836524);
					}
				
					if (!isset($this->frontend->config['config'])) {
						throw new Exception('Missing the configuration (config) in the TypoScript frontend setup.', 1356836323);
					}
						
					$this->sections[$section] = &$this->frontend->config['config'];
					break;
		}
		// vallidate
		if (!isset($this->sections[$section])) {
			throw new Exception(sprintf('The configuration section "%s" does not exist.', $section), 1356836240);
		}
	}
}

?>
