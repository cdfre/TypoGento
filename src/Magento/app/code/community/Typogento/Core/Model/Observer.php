<?php

/**
 * TypoGento observer
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Core_Model_Observer {
	
	/**
	 * Called before controller action started
	 * 
	 * Check if raw access is permitted to the Magento frontend
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function controllerActionPredispatch($observer) {
		$helper = Mage::helper('typogento_core');
		
		if (!$helper->isDirectAccessAllowed()) {
			
			$url = $helper->getRedirectUrl();
			
			Mage::app()->getResponse()->setRedirect($url);
		}
		
		return $this;
	}
	
	/**
	 * Called after TypoGento config has changed
	 * 
	 * @param unknown_type $observer
	 */
	public function adminSystemConfigChanged($observer) {
		//
		$helper = Mage::helper('typogento_core/typo3');
		// validate database connection
		Mage::unregister(Typogento_Core_Helper_Typo3::REGISTRY_KEY_DATABASE_CONNECTION_IS_VALID);
		if (!$helper->validateDatabaseConnection()) {
			Mage::throwException(Mage::helper('typogento_core')->__('TYPO3 database configuration is not valid.'));
		}
	}
}

