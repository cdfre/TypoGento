<?php

/**
 * TypoGento TYPO3 helper
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Wee_Typogento_Helper_Typo3 extends Mage_Core_Helper_Abstract {
	
	/**
	 * @var bool
	 */
	protected $_isLoggingOut = false;
	
	/**
	 * Logout frontend user in TYPO3 and Magento
	 * 
	 * @return void
	 */
	public function logout(){
		$this->_assertIsEnabled();
		
		if ($this->_isLoggingOut){
			return;
		}
	
		$this->_isLoggingOut = true;
	
		try {
			// logout TYPO3
			$GLOBALS['TSFE']->fe_user->logoff();
		} catch(Exception $e) {
			$this->_isLoggingOut = false;
			throw $e;
		}
		
		$this->_isLoggingOut = false;
	
	}
	
	/**
	 * Check TYPO3 frontend is enabled
	 * 
	 * @return bool Return true if TYPO3 frontend is enabled otherwise false
	 */
	public function isEnabled() {
		return (defined('TYPO3_MODE') && TYPO3_MODE === 'FE' ? true : false);
	}
	
	/**
	 * Get the current TYPO3 page configuration
	 * 
	 * @param string $key Configuration key
	 * @return mixed Return configuration array if no key is set otherwise it depends on the specified configuration
	 */
	public function getConfig($key = null) {
		$this->_assertIsEnabled();
		
		$config = $GLOBALS['TSFE']->config['config'];
		
		if (isset($key)) {
			if (isset($config[$key])) {
				return $config[$key];
			} else {
				return null;
			}
		} else {
			return $config;
		}
	}
	
	/**
	 * Get the TYPO3 frontend base URL
	 * 
	 * @return string The base URL of the TYPO3 frontend if enabled otherwise null
	 */
	public function getBaseUrl() {
		if (!$this->isEnabled()) {
			return null;
		}
		
		// get base url using tsfe
		$url = $this->getConfig('baseURL');
		// check if base url is set in tsfe
		if (isset($url)) {
			// return base url from tsfe
			return $url;
		} else {
			// using constant otherwise
			return t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
		}
	}
	
	/**
	 * Get the current TYPO3 page id
	 * 
	 * @return int The id of the current TYPO3 page
	 */
	public function getPageId() {
		$this->_assertIsEnabled();
		
		return intval($GLOBALS['TSFE']->id);
	}
	
	/**
	 * Get the TypoGento router
	 * 
	 * @return tx_weetypogento_router The TypoGento router
	 */
	public function getRouter() {
		$this->_assertIsEnabled();
		
		return t3lib_div::makeInstance('tx_weetypogento_router');
	}
	
	public function getRouteEnvironment() {
		$this->_assertIsEnabled();
		
		return t3lib_div::makeInstance('tx_weetypogento_routeEnvironment');
	}
	
	/**
	 * Assert TYPO3 frontend is anabled
	 * 
	 * @throws Exception Throws if TYPO3 frontend is not enabled
	 */
	protected function _assertIsEnabled() {
		if (!$this->isEnabled()) {
			throw new Exception('TYPO3 is not enabled');
		}
	}
}
