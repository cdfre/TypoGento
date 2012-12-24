<?php

/**
 * TypoGento frontend user model
 *
 * Provides direct access to the TYPO3 fe_users through Magento.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Authentication_Model_Typo3_Frontend_User extends Typogento_Replication_Model_Typo3_Frontend_User {
	
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
				|| !Mage::helper('typogento_core/typo3')->isFrontendActive()) {
				return false;
			}
			// 
			$authenticated = false;
			$exclude = array('tx_typogento_sv2');
			// 
			$lib = $GLOBALS['TSFE']->fe_user;
			$lib->formfield_status = 'login';
			$lib->formfield_uname = 'tx_typogento_login_name';
			$lib->formfield_uident = 'tx_typogento_login_password';
			$lib->formfield_chalvalue = 'tx_typogento_login_challenge';
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
				if ($service->getServiceKey() == 'tx_typogento_sv1') {
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
