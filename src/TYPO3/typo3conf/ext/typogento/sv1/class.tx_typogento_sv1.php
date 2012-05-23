<?php

require_once(t3lib_extmgm::extPath('sv').'class.tx_sv_auth.php');

/**
 * TypoGento frontend single sign-on service
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_sv1 extends tx_sv_auth {
	
	public $prefixId = 'tx_typogento_sv1';
	
	public $scriptRelPath = 'sv1/class.tx_typogento_sv1.php';
	
	public $extKey = 'typogento';
	
	/**
	 * Initialize service
	 * 
	 */
	public function init() {
		
		if (!parent::init()) {
			return false;
		}
		
		try {
			// init the magento autoloader
			t3lib_div::makeInstance('tx_typogento_autoloader');
			// init the magento application
			Mage::app();
			// load the frontend session
			Mage::getSingleton('core/session', array('name' => 'frontend'));
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
			$e = tx_typogento_div::exception('lib_data_source_not_supported', 
				array($this->db_user['table'])
			);
			$this->errorPush(T3_ERR_SV_GENERAL, $e->getMessage());
		}
	}
	
	/**
	 * Authenticate a user
	 * 
	 * @param array Data of user.
	 * @return boolean
	 */
	public function authUser($user) {
		// set default code
		$code = 100;
		// return if any errors or customer not set
		if ($this->getLastError() !== true 
			|| !isset($user['tx_typogento_customer'])) {
			return $code;
		}
		// perform authentication
		try {
			// load costumer
			$customer = $this->_getCustomer()->load($user['tx_typogento_customer']);
			// validate customer and password
			if ($customer->getId() && $customer->validatePassword($this->login['uident'])
				&& !($customer->getConfirmation() && $customer->isConfirmationRequired())) {
				// authentication successful
				$code = 200;
				// synchronize password
				$hash = md5($this->login['uident']);
				$user = $this->_getFrontendUser();
				$user->load($user['uid']);
				$user->setData('password', $hash);
				$user->save();
			}
		} catch (Exception $e) {
			// 
			$this->errorPush(T3_ERR_SV_GENERAL, $e->getMessage());
			// something went wrong
			return $code;
		}
	
		return $code;
	}
	
	/**
	 * Allow frontend user login by email
	 */
	public function getUser() {
		// skip if any errors
		if ($this->getLastError() !== true) {
			return false;
		}
		
		try {
			// get frontend user model
			$user = $this->_getFrontendUser();
			$user->load($this->login['uname'], 'email');
			// check if frontend user was found by email
			if ($user->getId()) {
				$record = $user->getData();
				//$record[]
				return $record;
			}
		} catch (Exception $e) {
			// log
		}
		
		return false;
	}
	
	/**
	 * Event handler for auto login
	 * 
	 * @param unknown_type $params
	 * @param unknown_type $pObj
	 */
	public function postUserLookUp($params, &$pObj) {
		// return if login has failed or frontend user customer link is not set
		if ($pObj->loginFailure || !$pObj->loginSessionStarted
			|| !isset($pObj->user['tx_typogento_customer'])) {
			return;
		}
		// try auto login
		try {
			// init the service
			if (!$this->init()) {
				throw tx_typogento_div::exception('lib_unknown_error');
			}
			// perform auto login
			$session = $this->_getSession();
			$session->loginById($pObj->user['tx_typogento_customer']);
		} catch (Exception $e) {
			$e = tx_typogento_div::exception('lib_sso_autologin_failed_error',
				array($pObj->user['uid'], $pObj->user['email'], $pObj->user['tx_typogento_customer']), $e
			);
			// @todo log exception
		}
	}
	
	protected function _getSession() {

		$session = Mage::getSingleton('customer/session');
		return $session;
	}
	
	protected function _getCustomer() {
		// reset customer
		$helper = t3lib_div::makeInstance('tx_typogento_magentoHelper');
		// get customer model
		$customer = Mage::getSingleton('customer/customer');
		$customer->setWebsiteId($helper->getWebsiteId());
		return $customer;
	}
	
	protected function _getFrontendUser() {
		// get frontend user model
		$user = Mage::getModel('typogento/typo3_frontend_user');
		return $user;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_auth_sv1.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_auth_sv1.php']);
}

?>
