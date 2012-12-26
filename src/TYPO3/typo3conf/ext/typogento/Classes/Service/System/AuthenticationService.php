<?php

namespace Tx\Typogento\Service\Frontend\User;

use \Tx\Typogento\Utility\LogUtility;
use \Tx\Typogento\Core\Bootstrap;

/**
 * Frontend user single sign-on service.
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class AuthenticationService extends \TYPO3\CMS\Sv\AuthenticationService {
	
	/**
	 * Initialize service
	 * 
	 */
	public function init() {
		// skip if parent init failed
		if (!parent::init()) {
			return false;
		}
		// init magento
		try {
			// init the autoloader
			Bootstrap::initialize();
			// init the application
			\Mage::app();
			// load the frontend session
			\Mage::getSingleton('core/session', array('name' => 'frontend'));
		} catch (\Exception $e) {
			$this->errorPush(T3_ERR_SV_GENERAL, $e->getMessage());
			LogUtility::error('[Authentication]'.$e->getMessage());
		}
		
		return ($this->getLastError() === true);
	}
	
	/**
	 * Initialize authentication service
	 *
	 * @param string Subtype of the service which is used to call the service.
	 * @param array Submitted login form data
	 * @param array Information array. Holds submitted form data etc.
	 * @param object Parent object
	 * @return void
	 */
	public function initAuth($mode, $loginData, $authInfo, $pObj) {
		// perform default init
		parent::initAuth($mode, $loginData, $authInfo, $pObj);
		// support only default frontend user data source
		if ($this->db_user['table'] != 'fe_users') {
			$e = new Exception(sprintf('The data source "%s" is not supported.', $this->db_user['table']), 1357260871);
			$this->errorPush(T3_ERR_SV_GENERAL, $e->getMessage());
			LogUtility::error('[Authentication] '.$e->getMessage());
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
			$customer = $this->getCustomer()->load($user['tx_typogento_customer']);
			// validate customer and password
			if ($customer->getId() && $customer->validatePassword($this->login['uident'])
				&& !($customer->getConfirmation() && $customer->isConfirmationRequired())) {
				// authentication successful
				$code = 200;
				// synchronize password
				$hash = md5($this->login['uident']);
				$user = $this->getFrontendUser();
				$user->load($user['uid']);
				$user->setData('password', $hash);
				$user->save();
			}
		} catch (\Exception $e) {
			// 
			$this->errorPush(T3_ERR_SV_GENERAL, $e->getMessage());
			LogUtility::error('[Authentication] '.$e->getMessage());
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
			$user = $this->getFrontendUser();
			$user->load($this->login['uname'], 'email');
			// check if frontend user was found by email
			if ($user->getId()) {
				$record = $user->getData();
				return $record;
			}
		} catch (\Exception $e) {
			LogUtility::error('[Authentication] '.$e->getMessage());
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
				throw new Exception('Unknown error.', 1357261369);
			}
			// perform auto login
			$session = $this->getSession();
			$session->loginById($pObj->user['tx_typogento_customer']);
		} catch (\Exception $e) {
			$e = new Exception(sprintf('Single sign-on has failed for "%s" (%s): %s', $pObj->user['email'], $pObj->user['uid'], $e->getMessage()), 1357261148, $e);
			LogUtility::error('[Authentication] '.$e->getMessage());
		}
	}
	
	protected function getSession() {
		$session = \Mage::getSingleton('customer/session');
		return $session;
	}
	
	protected function getCustomer() {
		// reset customer
		$helper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_typogento_magentoHelper');
		// get customer model
		$customer = \Mage::getSingleton('customer/customer');
		$customer->setWebsiteId($helper->getWebsiteId());
		return $customer;
	}
	
	protected function getFrontendUser() {
		// get frontend user model
		$user = \Mage::getModel('typogento_replication/typo3_frontend_user');
		return $user;
	}

}
?>