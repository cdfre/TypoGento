<?php

/**
 * TypoGento SOAP interface
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_soapinterface implements t3lib_Singleton {

	const WSDL_URI = '/api/soap/?wsdl';
	
	const CACHE_TAG = 'typogento_soap_results';
	
	protected $_client = null;
	
	protected $_session = null;
	
	protected $_resource = null;
	
	/** 
	 * @var tx_typogento_cache 
	 */
	protected $_cache = false;
	
	/**
	 * Constructor which needs Soap Connection Details
	 *
	 * @param string $url
	 * @param string $username
	 * @param string $password
	 */
	public function __construct() {
		$this->_cache = t3lib_div::makeInstance('tx_typogento_cache');
	}

	/**
	 * Magic function which enables SOAP Calls like: resource()->action();
	 *
	 * @param string $name
	 * @param array $params
	 * @return unknown
	 */
	public function __call($name, $params) {
		if ($this->_resource) {
			$resource = $this->_resource;
			$this->_resource = null;
			$result = $this->call($resource.'.'.$name, $params);

			return $result;
		} else {
			$this->_resource = $name;
			return $this;
		}
	}
	
	protected function _getHash($resource, $parameters) {
		ksort($parameters);
		$serialized = serialize(array_filter($parameters));
		return sha1($resource.$serialized);
	}

	/**
	 * call Soap Interface
	 *
	 * @param string $resource
	 * @param array $params
	 * @return unknown
	 */
	public function call($resource, $parameters = array()) {

		$hash = $this->_getHash($resource, $parameters);
		if (empty($hash)) {
			return null;
		}
		
		if ($this->_cache->has($hash)) {
			return $this->_cache->get($hash);
		} else {
			// lock request before start
			$lock = $this->_acquireLock($hash);
			try {
				// init session if not set
				if (!isset($this->client)
				|| !isset($this->_session)) {
					// get configuration helper
					$helper = t3lib_div::makeInstance('tx_typogento_magentoHelper');
					$url = $helper->getBaseUrl();
					$user = $helper->getApiAccount();
					$password = $helper->getApiPassword();
					// adds the wsdl path
					$url .= self::WSDL_URI;
					// xdebug work arround (see https://bugs.php.net/bug.php?id=34657)
					if (!@file_get_contents($url)) {
						throw tx_typogento_div::exception(
							'lib_wsdl_resource_not_found_error', array($url)
						);
					}
					// start soap client
					$this->client = new SoapClient($url, array('exceptions' => true, 'cache_wsdl' => WSDL_CACHE_MEMORY));
					$this->_session = $this->client->login($user, $password);
					// unset credentials
					unset($password);
					unset($user);
				}
				// perform soap query
				$result = $this->client->call($this->_session, $resource, $parameters);
				// cache the result
				$this->_cache->set($hash, $result, array(self::CACHE_TAG));
			} catch (Exception $e) {
				// release the lock
				$this->_releaseLock($lock);
				// throw exception
				throw tx_typogento_div::exception('lib_soap_request_failed_error', array(), $e);
			}
			// release the lock
			$this->_releaseLock($lock);
			// return the result
			return $result;
		}
		
		return null;
	}
	
	public function isAvailable() {
		try {
			// get configuration helper
			$helper = t3lib_div::makeInstance('tx_typogento_magentoHelper');
			$url = $helper->getBaseUrl();
			$user = $helper->getApiAccount();
			$password = $helper->getApiPassword();
			// adds the wsdl path
			$url .= self::WSDL_URI;
			// xdebug work arround (see https://bugs.php.net/bug.php?id=34657)
			if (!@file_get_contents($url)) {
				throw tx_typogento_div::exception(
					'lib_wsdl_resource_not_found_error', array($url)
				);
			}
			// start soap client
			$client = new SoapClient($url, array('exceptions' => true, 'cache_wsdl' => WSDL_CACHE_MEMORY));
			$client->login($user, $password);
			// unset credentials
			unset($password);
			unset($user);
		} catch (Exception $e) {
			// throw exception
			throw $e;
		}
		return true;
	}
	
	protected function _acquireLock($hash) {
		try {
			$lock = t3lib_div::makeInstance('t3lib_lock', $hash, 'simple');
			$lock->setEnableLogging(FALSE);
			$success = $lock->acquire();
		} catch (Exception $e) {
			//t3lib_div::sysLog('Locking: Failed to acquire lock: '.$e->getMessage(), 't3lib_formprotection_BackendFormProtection', t3lib_div::SYSLOG_SEVERITY_ERROR);
			return false;
		}

		return $lock;
	}
	
	protected function _releaseLock($lock) {
		$success = false;
			// If lock object is set and was acquired, release it:
		if (is_object($lock) && $lock instanceof t3lib_lock && $lock->getLockStatus()) {
			$success = $lock->release();
			$lock = null;
		}

		return $success;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_soapinterface.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_soapinterface.php']);
}

?>
