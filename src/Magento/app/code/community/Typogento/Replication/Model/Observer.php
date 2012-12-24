<?php

/**
 * TypoGento replication observer
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Replication_Model_Observer {
	
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
		$manager->registerProvider(
			Mage::getModel('typogento_replication/customer_provider')
		);
		$manager->registerProvider(
			Mage::getModel('typogento_replication/typo3_frontend_user_provider')
		);
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
		$helper = Mage::helper('typogento_core/typo3');
		// return if typo3 frontend not active
		if (!$helper->isFrontendActive()) {
			return $this;
		}
		// get customer
		$customer = $observer->getEvent()->getCustomer();
		// replicate frontend user
		$manager = Mage::getSingleton('typogento_replication/manager');
		$manager->replicate($customer);
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
		$helper = Mage::helper('typogento_core/typo3');
		// return if typo3 frontend not active
		if (!$helper->isFrontendActive()
			|| !$helper->validateDatabaseConnection()) {
			return $this;
		}
		// get customer
		$customer = $observer->getCustomer();
		// replicate changes
		try {
			$manager = Mage::getSingleton('typogento_replication/manager');
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
		$helper = Mage::helper('typogento_core/typo3');
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
}

