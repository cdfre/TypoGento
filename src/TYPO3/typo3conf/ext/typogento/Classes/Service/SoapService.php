<?php

namespace Tx\Typogento\Service;

use \Tx\Typogento\Utility\ConfigurationUtility;

/**
 * SOAP service
 *
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class SoapService implements \TYPO3\CMS\Core\SingletonInterface {

	const WSDL_URI = 'index.php/api/soap/?wsdl';
	
	const CACHE_TAG = 'typogento_soap_results';
	
	protected $client = null;
	
	protected $session = null;
	
	protected $resource = null;
	
	/** 
	 * @var tx_typogento_cache 
	 */
	protected $cache = false;
	
	/**
	 * Constructor which needs Soap Connection Details
	 *
	 * @param string $url
	 * @param string $username
	 * @param string $password
	 */
	public function __construct() {
		$this->cache = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Service\\CacheService');
	}

	/**
	 * Magic function which enables SOAP Calls like: resource()->action();
	 *
	 * @param string $name
	 * @param array $params
	 * @return unknown
	 */
	public function __call($name, $params) {
		if ($this->resource) {
			$resource = $this->resource;
			$this->resource = null;
			$result = $this->call($resource.'.'.$name, $params);

			return $result;
		} else {
			$this->resource = $name;
			return $this;
		}
	}
	
	protected function getHash($resource, $parameters) {
		ksort($parameters);
		$serialized = serialize(array_filter($parameters));
		return sha1($resource.$serialized);
	}
	
	protected function init() {
		// init client
		if (!isset($this->client)) {
			// get base url
			$url = ConfigurationUtility::getBaseUrl();
			// adds the wsdl uri
			$url .= self::WSDL_URI;
			try {
				// xdebug workaround (see http://bugs.xdebug.org/view.php?id=609)
				if (function_exists('xdebug_disable')) {
					xdebug_disable();
				}
				// start soap client
				$this->client = new \SoapClient($url, array('exceptions' => true, 'cache_wsdl' => WSDL_CACHE_MEMORY));
				// xdebug workaround
				if (function_exists('xdebug_enable')) {
					xdebug_enable();
				}
			} catch (\Exception $e) {
				// xdebug workaround
				if (function_exists('xdebug_enable')) {
					xdebug_enable();
				}
				// reset client
				$this->client = null;
				// re-throw exception
				throw $e;
			}
		}
		// init session
		if (!isset($this->session)) {
			try {
				// get credentials
				$user = ConfigurationUtility::getApiAccount();
				$password = ConfigurationUtility::getApiPassword();
				// get client session
				$this->session = $this->client->login($user, $password);
				// unset credentials
				unset($password);
				unset($user);
			} catch (\Exception $e) {
				// reset session
				$this->session = null;
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

		$hash = $this->getHash($resource, $parameters);
		if (empty($hash)) {
			return null;
		}
		
		if ($this->cache->has($hash)) {
			return $this->cache->get($hash);
		} else {
			// lock request before start
			$lock = $this->acquireLock($hash);
			try {
				// init interface
				$this->init();
				// perform soap query
				$result = $this->client->call($this->session, $resource, $parameters);
				// cache the result
				$this->cache->set($hash, $result, array(self::CACHE_TAG));
			} catch (\Exception $e) {
				// release the lock
				$this->releaseLock($lock);
				// throw exception
				throw new Exception(sprintf('The SOAP request has failed: %s', $e->getMessage()), 1356930394, $e);
			}
			// release the lock
			$this->releaseLock($lock);
			// return the result
			return $result;
		}
		
		return null;
	}
	
	public function isAvailable() {
		try {
			// init interface
			$this->init();
		} catch (\Exception $e) {
			// throw exception
			throw $e;
		}
		return true;
	}
	
	protected function acquireLock($hash) {
		try {
			$lock = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Locking\\Locker', $hash, 'simple');
			$lock->setEnableLogging(FALSE);
			$success = $lock->acquire();
		} catch (\Exception $e) {
			return false;
		}

		return $lock;
	}
	
	protected function releaseLock($lock) {
		$success = false;
			// If lock object is set and was acquired, release it:
		if (is_object($lock) 
			&& $lock instanceof \TYPO3\CMS\Core\Locking\Locker 
			&& $lock->getLockStatus()) {
			$success = $lock->release();
			$lock = null;
		}

		return $success;
	}
}
?>
