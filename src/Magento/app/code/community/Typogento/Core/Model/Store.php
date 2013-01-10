<?php

/**
 * TypoGento store model overrides
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 *
 */
class Typogento_Core_Model_Store extends Mage_Core_Model_Store {
	
	public function getBaseUrl($type = self::URL_TYPE_LINK, $secure = null) {
		if (!Mage::helper('typogento_core/typo3')->isFrontendActive() 
			|| ($type != self::URL_TYPE_LINK && $type != self::URL_TYPE_DIRECT_LINK)) {
			return parent::getBaseUrl($type, $secure);
		}
	
		$cacheKey = $type.'/'.(is_null($secure)?'null':($secure?'true':'false'));
		
		if (!isset($this->_baseUrlCache[$cacheKey])) {
			$url = Mage::helper('typogento_core/typo3')->getBaseUrl();
			$this->_baseUrlCache[$cacheKey] = rtrim($url, '/').'/';
		}
		
		return $this->_baseUrlCache[$cacheKey];
	}
}
