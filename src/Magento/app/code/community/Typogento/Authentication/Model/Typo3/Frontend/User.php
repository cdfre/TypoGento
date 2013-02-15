<?php

/**
 * Extends the TYPO3 frontend user model.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Authentication_Model_Typo3_Frontend_User extends Typogento_Replication_Model_Typo3_Frontend_User {
	
	/**
	 * Provides TYPO3 frontend user authentication in the box.
	 * 
	 * @param string $password
	 * @return boolean
	 * @todo Log exceptions
	 */
	public function authenticate($password) {
		// try authentication
		try {
			// if frontend user disabled, not found or detached
			if (!$this->getId() 
				|| !$this->isEnabled()
				|| !Mage::helper('typogento_core/typo3')->isFrontendActive()) {
				return false;
			}
			// result
			$authenticated = false;
			// excluded services
			$exclude = array('tx_typogento_sv1', 'tx_typogento_sv2');
			// retrive authentication
			$authentication = $GLOBALS['TSFE']->fe_user;
			// create login
			$login = array(
				'status' => 'login',
				'uname' => $this->getData('username'),
				'uident' => $password,
				'chalvalue' => ''
			);
			// process login
			$login = $authentication->processLoginData($login, 'normal');
			// retrive user data
			$record = $this->getData();
			// iterate services
			while (is_object($service = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstanceService('auth', 'authUserFE', $exclude))) {
				// exclude processed
				$exclude[] = $service->getServiceKey();
				// initialize current
				$service->initAuth('authUserFE', $login, $info, $authentication);
				// perform authentication
				$result = $service->authUser($record);
				// cast result
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
				// unset current
				unset($service);
			}
		} catch (Exception $e) {
			// something went wrong
			$authenticated = false;
		}
		// return result
		return $authenticated;
	}

}
