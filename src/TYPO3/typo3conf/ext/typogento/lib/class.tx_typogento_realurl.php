<?php

/**
 * TypoGento RealURL adapter
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_realurl {
	
	/**
	 * Rewrite products
	 *
	 * Adds speaking URLs for Magento products.
	 *
	 * @param array $params
	 * @param object tx_realurl $ref
	 * @return string
	 */
	public function productRewrite(array $params, tx_realurl $ref) {
		return $this->_getUrlKeyRewrite(
			'typogento_catalog_product', 
			$params['value'], 
			$params['decodeAlias']
		);
	}
	
	/**
	 * Rewrite Category
	 *
	 * @param array $params
	 * @param object tx_realurl $ref
	 * @return string
	 */
	public function categoryRewrite(array $params, tx_realurl $ref) {
		return $this->_getUrlKeyRewrite(
			'typogento_catalog_category', 
			$params['value'], 
			$params['decodeAlias']
		);
	}
	
	/**
	 * Get hash from data
	 * 
	 * Uses vprintf to build a hash.
	 * 
	 * @param string $format Format string (printf) for the hash
	 * @param array $data Data array to hash
	 */
	protected function _getHash($format, $data) {
		$string = vsprintf($format, $data);
		return sha1($string);
	}
	
	/**
	 * Resolve a value to its alias or key
	 * 
	 * This requires a method "urlkeys" on the requested SOAP resource 
	 * which must deliver an array with all ids as key and one 
	 * alias as its value.
	 * 
	 * @todo Make caching optional
	 * @todo Maybe Magento delivers here always (not optional) unique values on both sites
	 * @todo Fetching all source data at once using soap might be a problem with a high total amount of products
	 * @todo The SOAP call for products would delivers product variants also
	 * @todo May be using Magento's default info methods is better
	 * 
	 * @param string $resource The name of the SOAP resource
	 * @param unknown_type $value The value to rewrite
	 * @param bool $decode The direction of rewriting
	 */
	protected function _getUrlKeyRewrite($resource, $value, $decode = false) {
		$cache = t3lib_div::makeInstance('tx_typogento_cache');
		
		$resource .= '.urlkeys';
		$resourceHash = $this->_getHash('%s', array($resource));
		$valueHash = $this->_getHash('%s-%s-%b', array($resource, $value, $decode));
		
		// fetch rewrites and cache them if this is not already done
		if (!$cache->has($valueHash)) {
			// lock SOAP resource
			$lock = $this->_acquireLock($source);
			$soap = t3lib_div::makeInstance('tx_typogento_soapinterface');
			// fetch rewrite data
			$rewrites = $soap->call($resource);
			// temporary lookup table to force unique aliases
			$duplicates = array();
			// iterate rewrites
			foreach ($rewrites as $key => &$alias) {
				// force unique aliases
				if (!isset($duplicates[$alias])) {
					$counter = $duplicates[$alias] = 0;
				} else {
					$alias .= '-'.++$duplicates[$alias];
				}
				// create hash of both rewrite values
				$keyHash = $this->_getHash('%s-%s-%b', array($resource, $key, false));
				$aliasHash = $this->_getHash('%s-%s-%b', array($resource, $alias, true));
				// cache both values using their hashes
				$cache->set($keyHash, $alias);
				$cache->set($aliasHash, $key);
			}
			// set caching flag for resource
			$cache->set($resourceHash, $resourceHash);
			// release lock after fetching the rewrites and caching them
			$this->_releaseLock($lock);
		}
		// return the result
		if ($cache->has($valueHash)) {
			return $cache->get($valueHash);
		}
		
		return null;
	}
	
	/**
	 * Helper for locking resources
	 * 
	 * @param string $name Name of the resource to lock
	 * @return t3lib_lock|false The lock object or false if locking has failed
	 */
	protected function _acquireLock($name) {
		$hash = $this->_getHash('%s', array($name));
		try {
			$lock = t3lib_div::makeInstance('t3lib_lock', $hash, 'simple');
			$lock->setEnableLogging(false);
			$success = $lock->acquire();
		} catch (Exception $e) {
			return false;
		}
	
		return $lock;
	}
	
	/**
	 * Helper for release resources
	 *
	 * @param string $name Name of the resource to lock
	 * @return t3lib_lock|false The lock object or false if locking has failed
	 */
	protected function _releaseLock($lock) {
		$success = false;
		// If lock object is set and was acquired, release it:
		if (is_object($lock) && $lock instanceof t3lib_lock && $lock->getLockStatus()) {
			$success = $lock->release();
			unset($lock);
		}
	
		return $success;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_realurl.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_realurl.php']);
}

?>