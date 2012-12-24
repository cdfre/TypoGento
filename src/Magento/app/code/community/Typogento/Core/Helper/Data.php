<?php

/**
 * TypoGento data helper
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Core_Helper_Data extends Mage_Core_Helper_Abstract {
	
	const XML_PATH_ALLOW_DIRECT_ACCESS  = 'typogento/config/allow_direct_access';
	const XML_PATH_USER_AGENTS_REGEX    = 'typogento/config/user_agents_regex';
	const XML_PATH_REDIRECT_URL         = 'typogento/config/redirect_url';
	
	public function getRedirectUrl() {
		return Mage::getStoreConfig(self::XML_PATH_REDIRECT_URL, Mage::app()->getStore());
	}
	
	public function getUserAgentsRegex() {
		return Mage::getStoreConfig(self::XML_PATH_USER_AGENTS_REGEX, Mage::app()->getStore());
	}
	
	public function isDirectAccessAllowed() {
		$helper = Mage::helper('typogento_core/typo3');
		$store = Mage::app()->getStore();
		$request = Mage::app()->getRequest();
		
		if ($helper->isFrontendActive() || $store->isAdmin() || $request->getModuleName() === 'api') {
			return true;
		}
		
		if (!Mage::getStoreConfig(self::XML_PATH_ALLOW_DIRECT_ACCESS, $store)) {
			$regex = $this->getUserAgentsRegex();
			
			if (!empty($_SERVER['HTTP_USER_AGENT']) && !empty($regex)) {
				if (strpos($regex, '/', 0) === false) {
					$regex = '/' . $regex . '/';
				}
				if (@preg_match($regex, $_SERVER['HTTP_USER_AGENT'])) {
					return true;
				}
			}
			
			return false;
		} else {
			return true;
		}
	}

}
