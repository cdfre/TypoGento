<?php

/**
 * TypoGento observer model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Wee_Typogento_Model_Observer extends Mage_Core_Model_Abstract {
	
	/**
	 * Set if the request is being made via SOAP or XMLRPC
	 *
	 * @var boolean
	 */
	protected $_apiRequest;

	/**
	 * Called by Customer Login, create TYPO3 fe_users Session
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public static function loginEvent($observer) {
		if (!Mage::helper('typogento/typo3')->isEnabled()) {
			return;
		}

		$event = $observer->getEvent();

		/*@var $customer Mage_Customer_Model_Customer */
		$customer = $event->getCustomer();

		if($customer->getTypo3_uid()){

			$feUsers = Mage::getSingleton('typogento/typo3_frontend_user');
			$tempuser = $feUsers->getUserById($customer->getTypo3_uid());

			$GLOBALS['TSFE']->fe_user->createUserSession($tempuser);
		}

	}

	/**
	 * Called by Customer Logout, kills TYPO3 fe_user Session
	 *
	 * @param unknown_type $observer
	 */
	public static function logoutEvent($observer) {
		if (!Mage::helper('typogento/typo3')->isEnabled()) {
			return;
		}
		
		Mage::helper('typogento/typo3')->logout();
	}
	
	/**
	 * Create or update an TYPO3 Frontend User
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function customerSaveAfterEvent($observer) {

		if (!Mage::helper('typogento/typo3')->isEnabled()) {
			return;
		}

		// no TYPO3 db data given -> nothing to do
		if (!Mage::getStoreConfig('typogento/typo3_db/host') 
		|| !Mage::getStoreConfig('typogento/typo3_db/username') 
		|| !Mage::getStoreConfig('typogento/typo3_db/password')
		|| !Mage::getStoreConfig('typogento/typo3_db/dbname')) {
			return;
		}

		$customer = $observer->getCustomer();

		// assign the fields
		$fields = array (
			'username' => $customer->getData('email'), 
			'name' => $customer->getData('lastname'), 
			'firstname' => $customer->getData('firstname'), 
			'email' => $customer->getData('email'), 
			'password' => $customer->getData('password'), 
			'usergroup' => Mage::getStoreConfig('typogento/typo3_fe/group_uid'), 
			'pid' => Mage::getStoreConfig('typogento/typo3_fe/users_uid'), 
			'tx_fbmagento_id' => $customer->getId() 
		);

		try {
			// get fe_users Model
			$feUsers = Mage::getSingleton('typogento/typo3_frontend_user');
			$customer->load($customer->getId());
				
			if ($customer->getTypo3Uid()) {
				$feUsers->setId($customer->getTypo3Uid());
			}
				
			foreach($fields as $key => $value){
				$feUsers->setData($key, $value);
			}
				
			$feUsers->save();
			$customer->setData('typo3_uid', $feUsers->getData('uid'));
			$customer->getResource()->saveAttribute($customer, 'typo3_uid');
		} catch (Exception $e) {
			Mage::log($e->getMessage());
		}
	}

	/**
	 * Save typo3 group id
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function customerGroupSaveBefore($observer) {

		$observer->getObject()->setData('typo3_group_id', intval(Mage::app()->getRequest()->getParam('typo3_group_id')));

	}

	/**
	 * Check if raw access is permitted to the magento frontend
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function controllerActionPredispatch($observer) {
		if (Mage::helper('typogento/typo3')->isEnabled()) return;
		
		if (!Mage::app()->getStore()->isAdmin() && ! $this->_isApiRequest()) {
			if (!Mage::getStoreConfig('typogento/config/allow_direct_access')) {
				if ($uaRegex = Mage::getStoreConfig('typogento/config/user_agents_regex')) {
					if ($this->_checkUserAgentAgainstRegexps($uaRegex)) {
						return;
					}
				}
				Mage::app()->getResponse()->setRedirect(Mage::getStoreConfig('typogento/config/redirect_url'));
			}
		}
	}

	/**
	 * Return true if the request is being made via SOAP or XMLRPC
	 *
	 * @return boolean
	 */
	protected function _isApiRequest() {
		return Mage::app()->getRequest()->getModuleName() === 'api';
	}

	/**
	 * Match the User Agent Header value agains the given regex
	 *
	 * @param string $regexp
	 * @return bool
	 */
	protected function _checkUserAgentAgainstRegexps($regexp) {
		if (!empty($_SERVER['HTTP_USER_AGENT'])) {
			if (!empty($regexp)) {
				if (false === strpos($regexp, '/', 0)) {
					$regexp = '/' . $regexp . '/';
				}
				if (@preg_match($regexp, $_SERVER['HTTP_USER_AGENT'])) {
					return true;
				}
			}
		}
		return false;
	}
}

