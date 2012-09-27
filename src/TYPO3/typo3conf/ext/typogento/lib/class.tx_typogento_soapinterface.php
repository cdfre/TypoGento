<?php

/**
 * TypoGento SOAP interface
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_soapinterface implements t3lib_Singleton {

	const WSDL_URI = 'index.php/api/soap/?wsdl';
	
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
	
	protected function _init() {
		// init client
		if (!isset($this->_client)) {
			// get configuration helper
			$helper = t3lib_div::makeInstance('tx_typogento_magentoHelper');
			// get base url
			$url = $helper->getBaseUrl();
			// adds the wsdl uri
			$url .= self::WSDL_URI;
			try {
				// xdebug workaround (see http://bugs.xdebug.org/view.php?id=609)
				if (function_exists('xdebug_disable')) {
					xdebug_disable();
				}
				// start soap client
				$this->_client = new SoapClient($url, array('exceptions' => true, 'cache_wsdl' => WSDL_CACHE_MEMORY));
				// xdebug workaround
				if (function_exists('xdebug_enable')) {
					xdebug_enable();
				}
			} catch (Exception $e) {
				// xdebug workaround
				if (function_exists('xdebug_enable')) {
					xdebug_enable();
				}
				// reset client
				$this->_client = null;
				// re-throw exception
				throw $e;
			}
		}
		// init session
		if (!isset($this->_session)) {
			try {
				// get configuration helper
				$helper = t3lib_div::makeInstance('tx_typogento_magentoHelper');
				// get credentials
				$user = $helper->getApiAccount();
				$password = $helper->getApiPassword();
				// get client session
				$this->_session = $this->_client->login($user, $password);
				// unset credentials
				unset($password);
				unset($user);
			} catch (Exception $e) {
				// reset session
				$this->_session = null;
				// re-throw exception
				throw $e;
			}
		}
	}

	/**
	 * Call SOAP interface
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
				// init interface
				$this->_init();
				// perform soap query
				$result = $this->_client->call($this->_session, $resource, $parameters);
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
			// init interface
			$this->_init();
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
