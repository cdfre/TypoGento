<?php

namespace Tx\Typogento\Service;

/**
 * Cache service
 *
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class CacheService implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * Cache handler
	 *
	 * @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface 
	 */
	protected $handler = null;

	/**
	 * Constructor
	 *
	 * Initializes the TYPO3 caching framework.
	 */
	public function __construct() {
		$this->initialize();
	}

	/**
	 * Get data
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		return $this->handler->get($key);
	}

	/**
	 * Set data
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function set($key, &$value, $tags = array(), $lifetime = 0) {
		return $this->handler->set($key, $value, $tags, $lifetime);
	}

	/**
	 * Has data
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has($key) {
		return $this->handler->has($key);
	}

	/**
	 * Remove data
	 *
	 * @param string $key
	 * @return bool
	 */
	public function remove($key) {
		return $this->handler->remove($key);
	}
	
	/**
	 * Remove data by tag
	 *
	 * @param string $key
	 * @return bool
	 */
	public function flushByTag($tag) {
		return $this->handler->flushByTag($tag);
	}
	
	/**
	 * Initialize cache instance to be ready to use
	 *
	 * @return void
	 */
	protected function initialize() {
		//
		\TYPO3\CMS\Core\Cache\Cache::initializeCachingFramework();
		//
		try {
			$this->handler = $GLOBALS['typo3CacheManager']->getCache('typogento');
		} catch (\TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException $e) {
			//
			$this->handler = $GLOBALS['typo3CacheFactory']->create(
				'typogento',
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['typogento']['frontend'],
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['typogento']['backend'],
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['typogento']['options']
			);
		}
	}
}
?>
