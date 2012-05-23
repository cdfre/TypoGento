<?php

/**
 * TypoGento TYPO3 helper
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Core_Helper_Typo3 extends Mage_Core_Helper_Abstract {
	
	const XML_PATH_DATABASE_HOST                    = 'typogento/typo3_db/host';
	const XML_PATH_DATABASE_USER                    = 'typogento/typo3_db/username';
	const XML_PATH_DATABASE_PASSWORD                = 'typogento/typo3_db/password';
	const XML_PATH_DATABASE_NAME                    = 'typogento/typo3_db/dbname';
	const XML_PATH_DATABASE_CHARSET                 = 'typogento/typo3_db/charset';
	
	const XML_PATH_BACKEND_BASE_URL                 = 'typogento/typo3_be/base_url';
	
	const XML_PATH_FRONTEND_USERS_PAGE_ID           = 'typogento/typo3_fe/users_pid';
	const XML_PATH_FRONTEND_USERS_GROUP_ID          = 'typogento/typo3_fe/group_uid';
	
	const REGISTRY_KEY_DATABASE_CONNECTION_IS_VALID = 'typo3_database_connection_is_valid';
	
	public function getBackendBaseUrl() {
		return (string)Mage::getStoreConfig(self::XML_PATH_BACKEND_BASE_URL);
	}
	
	public function getFrontendUsersPageId() {
		return Mage::getStoreConfig(self::XML_PATH_FRONTEND_USERS_PAGE_ID, Mage::app()->getStore());
	}
	
	public function getFrontendUsersGroupId() {
		return Mage::getStoreConfig(self::XML_PATH_FRONTEND_USERS_GROUP_ID, Mage::app()->getStore());
	}
	
	public function getDatabaseHost() {
		return (string)Mage::getStoreConfig(self::XML_PATH_DATABASE_HOST);
	}
	
	public function getDatabaseName() {
		return (string)Mage::getStoreConfig(self::XML_PATH_DATABASE_NAME);
	}
	
	public function getDatabaseCharset() {
		return (string)Mage::getStoreConfig(self::XML_PATH_DATABASE_CHARSET);
	}
	
	public function getDatabaseUser() {
		return (string)Mage::getStoreConfig(self::XML_PATH_DATABASE_USER);
	}
	
	public function getDatabasePassword() {
		return (string)Mage::getStoreConfig(self::XML_PATH_DATABASE_PASSWORD);
	}
	
	/**
	 * Validate the database connection
	 *
	 * @return boolean
	 */
	public function validateDatabaseConnection() {
		// check if result is registered
		if (Mage::registry(self::REGISTRY_KEY_DATABASE_CONNECTION_IS_VALID) === null) {
			// set result
			$valid = false;
			// 
			try {
				// test database connection
				$database = Mage::getResourceModel('typogento/typo3_replication_link')
					->getReadConnection()
					->fetchOne('SELECT database();');
				// set result
				$valid = ($database == $this->getDatabaseName());
			} catch (Exception $e) {}
			// register result
			Mage::register(self::REGISTRY_KEY_DATABASE_CONNECTION_IS_VALID, $valid);
		}
		// return registered result
		return (bool)Mage::registry(self::REGISTRY_KEY_DATABASE_CONNECTION_IS_VALID);
	}
	
	/**
	 * Check TYPO3 frontend is enabled
	 * 
	 * @return bool Return true if TYPO3 frontend is enabled otherwise false
	 */
	public function isFrontendActive() {
		return (defined('TYPO3_MODE') && TYPO3_MODE === 'FE' ? true : false);
	}
	
	/**
	 * Get the current TYPO3 page configuration
	 * 
	 * @param string $key Configuration key
	 * @return mixed Return configuration array if no key is set otherwise it depends on the specified configuration
	 */
	public function getPageConfiguration($key = null) {
		$this->_assertIsFrontendActive();
		
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
		if (!$this->isFrontendActive()) {
			return null;
		}
		
		// get base url using tsfe
		$url = $this->getPageConfiguration('baseURL');
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
		$this->_assertIsFrontendActive();
		
		return intval($GLOBALS['TSFE']->id);
	}
	
	public function getWebsiteId() {
		$this->_assertIsFrontendActive();
		
		return t3lib_div::makeInstance('tx_typogento_magentoHelper')->getWebsiteId();
	}
	
	/**
	 * Get the TypoGento router
	 * 
	 * @return tx_typogento_router The TypoGento router
	 */
	public function getRouter() {
		$this->_assertIsFrontendActive();
		
		return t3lib_div::makeInstance('tx_typogento_router');
	}
	
	public function getRouteEnvironment() {
		$this->_assertIsFrontendActive();
		
		return t3lib_div::makeInstance('tx_typogento_routeEnvironment');
	}
	
	/**
	 * Assert TYPO3 frontend is anabled
	 * 
	 * @throws Exception Throws if TYPO3 frontend is not enabled
	 */
	protected function _assertIsFrontendActive() {
		if (!$this->isFrontendActive()) {
			throw new Exception($this->__('TYPO3 frontend is not active'));
		}
	}
}
