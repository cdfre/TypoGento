<?php

/**
 * TypoGento frontend user model
 *
 * Provides direct access to the TYPO3 fe_users through Magento.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Wee_Typogento_Model_Typo3_Frontend_User extends Mage_Core_Model_Abstract {
	
	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$this->_init('typogento/typo3_frontend_user');
	}
	
	public function isEnabled() {
		return (
			$this->getData('disabled') == 0 
			&& ($this->getData('starttime') == 0 
				|| $this->getData('starttime') > time())
			&& ($this->getData('endtime') == 0 
				|| $this->getData('endtime') < time())
		);
	}
	
	public function findEmailDuplicates() {
		return $this->_getResource()->findEmailDuplicates();
	}
	
	public function findCustomerDuplicates() {
		return $this->_getResource()->findCustomerDuplicates();
	}
	
	/**
	 * Authenticate frontend user
	 * 
	 * Provides TYPO3 fe_users authentication in the box.
	 * 
	 * @param unknown_type $customer
	 * @param unknown_type $password
	 */
	public function authenticate($password) {
		//
		try {
			// if frontend user disabled, not found or detached
			if (!$this->getId() || !$this->isEnabled()
				|| !Mage::helper('typogento/typo3')->isFrontendActive()) {
				return false;
			}
			// 
			$authenticated = false;
			$exclude = array('tx_weetypogento_sv2');
			// 
			$lib = $GLOBALS['TSFE']->fe_user;
			$lib->formfield_status = 'login';
			$lib->formfield_uname = 'tx_weetypogento_login_name';
			$lib->formfield_uident = 'tx_weetypogento_login_password';
			$lib->formfield_chalvalue = 'tx_weetypogento_login_challenge';
			$lib->getMethodEnabled = true;
			t3lib_div::_GETset($this->getData('username'), $lib->formfield_uname);
			t3lib_div::_GETset($password, $lib->formfield_uident);
			t3lib_div::_GETset('12345', $lib->formfield_chalvalue);
			//
			$info = $lib->getAuthInfoArray();
			$login = $lib->getLoginFormData();
			$record = $this->getData();
			// 
			while (is_object($service = t3lib_div::makeInstanceService('auth', 'authUserFE', $exclude))) {
				// exclude service
				$exclude[] = $service->getServiceKey();
				//
				if ($service->getServiceKey() == 'tx_weetypogento_sv1') {
					continue;
				}
				// initialize service
				$service->initAuth('authUserFE', $login, $info, $record);
				// authenticate
				$result = $service->authUser($record);
				$result = intval($result);
				// validate result
				if ($result >= 200) {
					$authenticated = true;
					break;
				} else if ($result >= 100) {
					continue;
				} else if ($result > 0) {
					$authenticated = true;
				} else {
					$authenticated = false;
					break;
				}
				unset($service);
			}
			unset($service);
		} catch (Exception $e) {
			return false;
		}
		
		return $authenticated;
	}

}
