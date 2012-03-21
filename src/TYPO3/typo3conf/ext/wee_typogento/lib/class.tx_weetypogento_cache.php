<?php

/**
 * TypoGento cache
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_weetypogento_cache implements t3lib_Singleton {

	/**
	 * Cache Handler
	 *
	 * @var t3lib_cache_frontend_Frontend 
	 */
	protected $_handler = null;

	/**
	 * Constructor
	 *
	 * Initializes the TYPO3 caching framework.
	 */
	public function __construct() {
		$this->_initializeCache();
	}

	/**
	 * Get data
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		return $this->_handler->get($key);
	}

	/**
	 * Set data
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function set($key, &$value, $tags = array(), $lifetime = 0) {
		return $this->_handler->set($key, $value, $tags, $lifetime);
	}

	/**
	 * Has data
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has($key) {
		return $this->_handler->has($key);
	}

	/**
	 * Remove data
	 *
	 * @param string $key
	 * @return bool
	 */
	public function remove($key) {
		return $this->_handler->remove($key);
	}
	
	/**
	 * Remove data by tag
	 *
	 * @param string $key
	 * @return bool
	 */
	public function flushByTag($tag) {
		return $this->_handler->flushByTag($tag);
	}
	
	/**
	 * Initialize cache instance to be ready to use
	 *
	 * @return void
	 */
	protected function _initializeCache() {
		//
		t3lib_cache::initializeCachingFramework();
		//
		try {
			$this->_handler = $GLOBALS['typo3CacheManager']->getCache('wee_typogento');
		} catch (t3lib_cache_exception_NoSuchCache $e) {
			//
			$this->_handler = $GLOBALS['typo3CacheFactory']->create(
				'wee_typogento',
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['wee_typogento']['frontend'],
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['wee_typogento']['backend'],
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['wee_typogento']['options']
			);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_cache.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_cache.php']);
}

?>
