<?php

/**
 * TypoGento store model overrides
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 *
 */
class Typogento_Core_Model_Store extends Mage_Core_Model_Store {
	
	public function getBaseUrl($type = self::URL_TYPE_LINK, $secure = null) {
		$typo3 = Mage::helper('typogento_core/typo3');
		$reflection = new ReflectionClass($this);
	
		$cacheKey = $type.'/'.(is_null($secure)?'null':($secure?'true':'false'));
		
		if (!isset($this->_baseUrlCache[$cacheKey])) {
			
			switch ($type) {
				case self::URL_TYPE_WEB:
					$secure = is_null($secure)?$this->isCurrentlySecure():(bool)$secure;
					$url = $this->getConfig('web/'.($secure?'secure':'unsecure').'/base_url');
					break;
				
				case self::URL_TYPE_LINK:
					if ($typo3->getBaseUrl()) {
						$url = $typo3->getBaseUrl();
					} else {
						$secure =(bool)$secure;
						$url = $this->getConfig('web/'.($secure?'secure':'unsecure').'/base_link_url');
						$url = $this->_updatePathUseRewrites($url);
						$url = $this->_updatePathUseStoreView($url);
					}
					break;

				case $reflection->getConstant('URL_TYPE_DIRECT_LINK') !== false && self::URL_TYPE_DIRECT_LINK == $type:
					if ($typo3->getBaseUrl()) {
						$url = $this->getTypo3BaseUrl ();
					} else {
						$secure = (bool)$secure;
						$url = $this->getConfig('web/'.($secure?'secure':'unsecure').'/base_link_url');
						$url = $this->_updatePathUseRewrites($url);
					}
					break;
				
				case self::URL_TYPE_SKIN:
				case self::URL_TYPE_MEDIA:
				case self::URL_TYPE_JS:
					$secure = is_null($secure)?$this->isCurrentlySecure():(bool)$secure;
					$url = $this->getConfig('web/'.($secure?'secure':'unsecure').'/base_'.$type.'_url');
					break;
				
				default :
					throw Mage::exception('Mage_Core', Mage::helper('core')->__('Invalid base url type'));
			}
			
			$this->_baseUrlCache[$cacheKey] = rtrim($url, '/').'/';
		}
		
		return $this->_baseUrlCache[$cacheKey];
	}

}
