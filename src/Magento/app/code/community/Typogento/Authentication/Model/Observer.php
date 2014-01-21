<?php

/**
 * TypoGento observer
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Authentication_Model_Observer {
	
	/**
	 * Called after customer login
	 * 
	 * Performs auto login for the TYPO3 frontend user and starts the replication.
	 * 
	 * @param Varien_Event_Observer $observer
	 */
	public function customerLogin($observer) {
		// create helper
		$helper = Mage::helper('typogento_core/typo3');
		// return if typo3 frontend not active
		if (!$helper->isFrontendActive()) {
			return $this;
		}
		// check login status
		if (isset($GLOBALS['TSFE']->fe_user->user['uid'])) {
			return $this;
		}
		// get customer
		$customer = $observer->getEvent()->getCustomer();
		// get frontend user model
		$user = Mage::getSingleton('typogento_replication/typo3_frontend_user');
		// load frontend user
		$user->load($customer->getId(), 'tx_typogento_customer');
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
	public function customerLogout($observer) {
		// create helper
		$helper = Mage::helper('typogento_core/typo3');
		// return if typo3 frontend not active
		if (!$helper->isFrontendActive()) {
			return $this;
		}
		// check login status
		if (!isset($GLOBALS['TSFE']->fe_user->user['uid'])) {
			return $this;
		}
		// get logoff hooks
		$hooks = &$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing'];
		// remove typogento handler
		if (is_array($hooks)) {
			$hook = $hooks['typogento'];
			unset($hooks['typogento']);
		}
		// logout typo3
		try {
			$GLOBALS['TSFE']->fe_user->logoff();
		} catch(Exception $e) {
			throw $e;
		}
		// restore typogento handler
		if (isset($hook)) {
			$hooks['typogento'] = $hook;
		}
		return $this;
	}
}

