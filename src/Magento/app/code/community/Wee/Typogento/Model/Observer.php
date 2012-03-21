<?php

/**
 * TypoGento observer
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Wee_Typogento_Model_Observer {
	
	/**
	 * Called during initialization of the replication manager
	 * 
	 * Registers the account provisioning provider.
	 * 
	 * @param Varien_Event_Observer $observer
	 */
	public function replicationManagerInitialize(Varien_Event_Observer $observer) {
		// register account replication provider
		$manager = $observer->getEvent()->getManager();
		$customer = Mage::getModel('typogento/replication_provider_accounts', 
			Wee_Typogento_Model_Replication_Provider_Accounts::PROVIDER_ID_MAGENTO_CUSTOMER
		);
		$user = Mage::getModel('typogento/replication_provider_accounts', 
			Wee_Typogento_Model_Replication_Provider_Accounts::PROVIDER_ID_TYPO3_FRONTEND_USER
		);
		$manager->registerProvider($customer);
		$manager->registerProvider($user);
		return $this;
	}
	
	/**
	 * Called after customer login
	 * 
	 * Performs auto login for the TYPO3 frontend user and starts the replication.
	 * 
	 * @param Varien_Event_Observer $observer
	 */
	public static function customerLogin($observer) {
		//
		$helper = Mage::helper('typogento/typo3');
		// return if typo3 frontend not active
		if (!$helper->isFrontendActive()) {
			return $this;
		}
		// get customer
		$customer = $observer->getEvent()->getCustomer();
		// replicate frontend user
		$manager = Mage::getSingleton('typogento/replication_manager');
		$manager->replicate($customer);
		// get frontend user model
		$user = Mage::getSingleton('typogento/typo3_frontend_user');
		// load frontend user
		$user->load($customer->getId(), 'tx_weetypogento_customer');
		// validate frontend user
		if ($user->getId()) {
			$record = $user->getData();
			$GLOBALS['TSFE']->fe_user->createUserSession($record);
		}
	}

	/**
	 * Called after customer logout
	 *
	 * Forces the TYPO3 frontend user logoff.
	 * 
	 * @param unknown_type $observer
	 */
	public static function customerLogout($observer) {
		//
		$helper = Mage::helper('typogento/typo3');
		// return if typo3 frontend not active
		if (!$helper->isFrontendActive()) {
			return $this;
		}
		// get logoff hooks
		$hooks = &$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing'];
		// remove typogento handler
		if (is_array($hooks)) {
			$hook = $hooks['wee_typogento'];
			unset($hooks['wee_typogento']);
		}
		// logout typo3
		try {
			$GLOBALS['TSFE']->fe_user->logoff();
		} catch(Exception $e) {
			throw $e;
		}
		// restore typogento handler
		if (isset($hook)) {
			$hooks['wee_typogento'] = $hook;
		}
		return $this;
	}
	
	/**
	 * Called after customer saved
	 * 
	 * Replicates the Magento customer.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function customerSaveAfter($observer) {
		//
		$helper = Mage::helper('typogento/typo3');
		// return if typo3 frontend not active
		if (!$helper->isFrontendActive()
			|| !$helper->validateDatabaseConnection()) {
			return $this;
		}
		// get customer
		$customer = $observer->getCustomer();
		// replicate changes
		try {
			$manager = Mage::getSingleton('typogento/replication_manager');
			$manager->replicate($customer);
		} catch (Exception $e) {
			Mage::log($e->getMessage());
		}
		return $this;
	}
	
	/**
	 * Called after customer address saved
	 *
	 * Replicates the Magento customer.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function customerAddressSaveAfter($observer) {
		//
		$helper = Mage::helper('typogento/typo3');
		// return if typo3 frontend not active
		if (!$helper->isFrontendActive()
			|| !$helper->validateDatabaseConnection()) {
			return $this;
		}
		// get customer
		$id = $observer->getCustomerAddress()->getCustomerId();
		$customer = Mage::getModel('customer/customer');
		$customer->setId($id);
		// replicate changes
		try {
			$manager = Mage::getSingleton('typogento/replication_manager');
			$manager->replicate($customer);
		} catch (Exception $e) {
			Mage::log($e->getMessage());
		}
		return $this;
	}
	
	/**
	 * Called before controller action started
	 * 
	 * Check if raw access is permitted to the Magento frontend
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function controllerActionPredispatch($observer) {
		$helper = Mage::helper('typogento');
		
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
		$helper = Mage::helper('typogento/typo3');
		// validate database connection
		Mage::unregister(Wee_Typogento_Helper_Typo3::REGISTRY_KEY_DATABASE_CONNECTION_IS_VALID);
		if (!$helper->validateDatabaseConnection()) {
			Mage::throwException(Mage::helper('typogento')->__('TYPO3 database configuration is not valid.'));
		}
	}
}

