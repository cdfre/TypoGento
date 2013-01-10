<?php

namespace Tx\Typogento\Hook;

/**
 * RealURL hooks
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class RealUrlHook {
	
	/**
	 * Adds speaking URLs for Magento products.
	 *
	 * @param array $params
	 * @param object tx_realurl $ref
	 * @return string
	 */
	public function productRewrite(array $params, tx_realurl $ref) {
		return $this->getUrlKeyRewrite(
			'typogento_catalog_product', 
			$params['value'], 
			$params['decodeAlias']
		);
	}
	
	/**
	 * Adds speaking URLs for Magento categories.
	 *
	 * @param array $params
	 * @param object tx_realurl $ref
	 * @return string
	 */
	public function categoryRewrite(array $params, tx_realurl $ref) {
		return $this->getUrlKeyRewrite(
			'typogento_catalog_category', 
			$params['value'], 
			$params['decodeAlias']
		);
	}
	
	/**
	 * Uses vprintf to build a hash.
	 * 
	 * @param string $format Format string (printf) for the hash
	 * @param array $data Data array to hash
	 */
	protected function getHash($format, $data) {
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
	 * @todo Check if Magento delivers always (not optional) unique values on both sites
	 * @todo Fetching all source data at once using soap might be a problem with a high total amount of products
	 * @todo The SOAP call for products would delivers product variants also
	 * @todo Maybe it's better to useMagento's default info methods instead
	 * 
	 * @param string $resource The name of the SOAP resource
	 * @param unknown_type $value The value to rewrite
	 * @param bool $decode The direction of rewriting
	 */
	protected function getUrlKeyRewrite($resource, $value, $decode = false) {
		$cache = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Service\\CacheService');
		
		$resource .= '.urlkeys';
		$resourceHash = $this->getHash('%s', array($resource));
		$valueHash = $this->getHash('%s-%s-%b', array($resource, $value, $decode));
		
		// fetch rewrites and cache them if this is not already done
		if (!$cache->has($valueHash)) {
			// lock SOAP resource
			$lock = $this->acquireLock($resource);
			$soap = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Service\\SoapService');
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
				$keyHash = $this->getHash('%s-%s-%b', array($resource, $key, false));
				$aliasHash = $this->getHash('%s-%s-%b', array($resource, $alias, true));
				// cache both values using their hashes
				$cache->set($keyHash, $alias);
				$cache->set($aliasHash, $key);
			}
			// set caching flag for resource
			$cache->set($resourceHash, $resourceHash);
			// release lock after fetching the rewrites and caching them
			$this->releaseLock($lock);
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
	protected function acquireLock($name) {
		$hash = $this->getHash('%s', array($name));
		try {
			$lock = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Locking\\Locker', $hash, 'simple');
			$lock->setEnableLogging(false);
			$success = $lock->acquire();
		} catch (\Exception $e) {
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
	protected function releaseLock($lock) {
		$success = false;
		// If lock object is set and was acquired, release it:
		if (is_object($lock) 
			&& $lock instanceof \TYPO3\CMS\Core\Locking\Locker 
			&& $lock->getLockStatus()) {
			$success = $lock->release();
			unset($lock);
		}
	
		return $success;
	}
}
?>