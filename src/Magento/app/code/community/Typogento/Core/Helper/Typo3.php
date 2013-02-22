<?php

/**
 * TypoGento TYPO3 helper
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Core_Helper_Typo3 extends Mage_Core_Helper_Abstract {
	
	const XML_PATH_DATABASE_HOST                    = 'typo3/database/host';
	const XML_PATH_DATABASE_USER                    = 'typo3/database/user';
	const XML_PATH_DATABASE_PASSWORD                = 'typo3/database/password';
	const XML_PATH_DATABASE_NAME                    = 'typo3/database/name';
	const XML_PATH_DATABASE_CHARSET                 = 'typo3/database/charset';
	
	const XML_PATH_BACKEND_BASE_URL                 = 'typo3/backend/base_url';
	
	const REGISTRY_KEY_DATABASE_CONNECTION_IS_VALID = 'typo3_database_connection_is_valid';
	
	public function getBackendBaseUrl() {
		return (string)Mage::getStoreConfig(self::XML_PATH_BACKEND_BASE_URL);
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
	 * @todo Remove usage of replication module
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
				$database = Mage::getResourceModel('typogento_replication/link')
					->getReadConnection()
					->fetchOne('SELECT database();');
				// set result
				$valid = ($database == $this->getDatabaseName());
			} catch (Exception $e) {throw $e;}
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
			return \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR');
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
		
		return \Tx\Typogento\Utility\ConfigurationUtility::getWebsiteId();
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
