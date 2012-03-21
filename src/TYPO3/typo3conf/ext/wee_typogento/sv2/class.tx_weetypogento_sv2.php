<?php

require_once(t3lib_extmgm::extPath('sv').'class.tx_sv_auth.php');

/**
 * TypoGento frontend user provisioning service
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_weetypogento_sv2 extends tx_sv_auth {
	
	public $prefixId = 'tx_weetypogento_sv2';
	
	public $scriptRelPath = 'sv2/class.tx_weetypogento_sv2.php';
	
	public $extKey = 'wee_typogento';
	
	/**
	 * Initialize service
	 */
	public function init() {
		// sip if parent init failed
		if (!parent::init()) {
			return false;
		}
		
		try {
			// init the magento autoloader
			t3lib_div::makeInstance('tx_weetypogento_autoloader');
			// init the magento application
			Mage::app();
			// register post user lookup hook
			if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'])) {
				$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'] = array();
			}
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp']['wee_typogento_sv2'] =
				'EXT:wee_typogento/sv2/class.tx_weetypogento_sv2.php:&tx_weetypogento_sv2->postUserLookUp';
		} catch (Exception $e) {
			$this->errorPush(T3_ERR_SV_GENERAL, $e->getMessage());
		}
		
		return ($this->getLastError() === true);
	}
	
	/**
	 * Initialize authentication service
	 *
	 * @param	string		Subtype of the service which is used to call the service.
	 * @param	array		Submitted login form data
	 * @param	array		Information array. Holds submitted form data etc.
	 * @param	object		Parent object
	 * @return	void
	 */
	public function initAuth($mode, $loginData, $authInfo, $pObj) {
		// perform default init
		parent::initAuth($mode, $loginData, $authInfo, $pObj);
		// support only default frontend user data source
		if ($this->db_user['table'] != 'fe_users') {
			$e = tx_weetypogento_div::exception('lib_data_source_not_supported',
					array($this->db_user['table'])
			);
			$this->errorPush(T3_ERR_SV_GENERAL, $e->getMessage());
		}
	}
	
	/**
	 * Get frontend user
	 * 
	 * Provides automatic synchronization with Magento customers.
	 * 
	 * @return mixed user array or false
	 */
	public function getUser() {
		// get frontend user model
		$user = Mage::getModel('typogento/typo3_frontend_user');
		// load frontend user
		$user->load($this->login['uname'], 'email');
		// validate user
		if (!$user->getId()) {
			// discover possible customer
			$manager = Mage::getSingleton('typogento/replication_manager');
			$customer = $manager->discover($user);
			// validate customer
			if (isset($customer) && $customer->getId()) {
				// replicate customer
				$manager->replicate($customer);
				// 
				unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp']['wee_typogento_sv2']);
			}
		} else {
			return $user->getData();
		}
		// return data set
		return false;
	}
	
	/**
	 * Authenticate a user
	 *
	 * @param array Data of user.
	 * @return boolean
	 */
	public function authUser($user) {
		return 100;
	}
	
	/**
	 * Update frontend user password after sucessful login.
	 * 
	 * @param unknown_type $params
	 * @param unknown_type $pObj
	 */
	public function postUserLookUp($params, &$pObj) {
		// unregister hook
		unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp']['wee_typogento_sv2']);
		// skip if login failed
		if ($pObj->loginFailure
			|| !$pObj->loginSessionStarted
			|| !isset($pObj->user['tx_weetypogento_customer'])) {
			return;
		}
		try {
			// init
			if (!$this->init()) {
				throw tx_weetypogento_div::exception('lib_unknown_error');
			}
			// replicate frontend user
			$manager = Mage::getSingleton('typogento/replication_manager');
			$user = Mage::getModel('typogento/typo3_frontend_user');
			$user->setData($pObj->user);
			$manager->replicate($user);
		} catch (Exception $e) {
			// @todo logging
		}
	}
}

?>