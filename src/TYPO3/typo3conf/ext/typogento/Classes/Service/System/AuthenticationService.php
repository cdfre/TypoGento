<?php

namespace Tx\Typogento\Service\System;

use Tx\Typogento\Core\Bootstrap;

/**
 * Frontend user single sign-on service.
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class AuthenticationService extends \TYPO3\CMS\Sv\AuthenticationService {
	
	/**
	 * @var \TYPO3\CMS\Core\Log\LogManager
	 */
	protected $logger = null;
	
	/**
	 * 
	 * @var boolean
	 */
	protected $initialized = false;
	
	/**
	 * Initializes the service.
	 * 
	 * @return boolean
	 */
	public function init() {
		// skip if parent initialize fails
		if (!parent::init()) {
			return false;
		}
		// create logger
		$this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
		// init framework
		try {
			// start autoloader
			Bootstrap::initialize();
			// init application
			\Mage::app();
			// load frontend session
			\Mage::getSingleton('core/session', array('name' => 'frontend'));
		} catch (\Exception $e) {
			// create exception
			$exception = new \Exception(sprintf('Initializing failed: ', $e->getMessage()), 1360874540, $e);
			// log exception
			$this->logger->error($exception->getMessage());
			// push exception
			$this->errorPush(T3_ERR_SV_GENERAL, $exception->getMessage());
		}
		// return result
		return ($this->getLastError() === true);
	}
	
	/**
	 * Initializes the authentication service.
	 *
	 * @param string $mode Subtype of the service which is used to call the service.
	 * @param array $loginData Submitted login form data
	 * @param array $authInfo Information array. Holds submitted form data etc.
	 * @param object $pObj Parent object
	 * @return void
	 */
	public function initAuth($mode, $loginData, $authInfo, $pObj) {
		// perform default initialize
		parent::initAuth($mode, $loginData, $authInfo, $pObj);
		// support only default frontend user data source
		if ($this->db_user['table'] != 'fe_users' && $mode != 'processLoginDataFE') {
			// create exception
			$exception = new \Exception(
				sprintf('Initializing failed: Unsupported data source "%s".', $this->db_user['table']), 
				1357260871
			);
			// log exception
			$this->logger->error($exception->getMessage());
			// push exception
			$this->errorPush(T3_ERR_SV_GENERAL, $exception->getMessage());
		}
	}
	
	/**
	 * Authenticates a TYPO3 frontend user.
	 * 
	 * @todo Make password synchronisation configurable
	 * @param array Data of user.
	 * @return boolean
	 */
	public function authUser($user) {
		// set default code
		$code = 100;
		// return on errors or missing link
		if ($this->getLastError() !== true 
			|| !isset($user['tx_typogento_customer'])) {
			// authentication not possible
			return $code;
		}
		// perform authentication
		try {
			// retrive id
			$id = $user['tx_typogento_customer'];
			// retrive password
			$password = $user['uident'];
			// load customer
			$customer = $this->getCustomer()->load($id);
			// validate customer and password
			if ($customer->getId() && $customer->validatePassword($password)
				&& !($customer->getConfirmation() && $customer->isConfirmationRequired())) {
				// authentication successful
				$code = 200;
				// synchronize password
				$hash = md5($password);
				$user = $this->getFrontendUser()->load($user['uid']);
				$user->setData('password', $hash);
				$user->save();
			}
		} catch (\Exception $e) {
			// create exception
			$exception = new \Exception(
				sprintf('Authentication failed for user "%s" (%s): %s', $user['email'], $user['uid'], $e->getMessage()), 
				1360876296, $e
			);
			// log exception
			$this->logger->error($exception->getMessage());
		}
		// return result
		return $code;
	}
	
	/**
	 * Retrives frontend user by email.
	 * 
	 * @return mixed User array or false 
	 * @see \TYPO3\CMS\Sv\AuthenticationService::getUser()
	 */
	public function getUser() {
		// skip if any errors
		if ($this->getLastError() !== true) {
			return false;
		}
		// try to load
		try {
			// get frontend user wrapper
			$user = $this->getFrontendUser();
			// retrive frontend user email
			$email = $this->login['uname'];
			// load the frontend user
			$user->load($email, 'email');
			// check if frontend user was found by email
			if ($user->getId()) {
				// return frontend user data
				return $user->getData();
			}
		} catch (\Exception $e) {
			// create exception
			$exception = new \Exception(
				sprintf('Lookup failed for user "%s": %s', $this->login['uname'], $e->getMessage()), 
				1360876504, $e
			);
			// log exception
			$this->logger->error($e->getMessage());
		}
		// nothing found
		return false;
	}
	
	/**
	 * Starts the Magento customer session early.
	 * 
	 * This is just an entry point for the authentication process, to make sure init() is called before any other service.
	 *
	 * @param array $loginData Credentials that are submitted and potentially modified by other services
	 * @param string $passwordTransmissionStrategy Keyword of how the password has been hashed or encrypted before submission
	 * @return boolean
	 * @see ext_localconf.php
	 */
	public function processLoginData(array &$loginData, $passwordTransmissionStrategy) {
		// nothing processed
		return false;
	}
	
	/**
	 * Performs auto login for the Magento customer.
	 * 
	 * @param array $params
	 * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $pObj
	 * @see $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp']
	 */
	public function postUserLookUp($params, \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $pObj) {
		// check login status
		if ($pObj->loginFailure 
			|| !$pObj->loginSessionStarted 
			|| !isset($pObj->user['tx_typogento_customer'])
			|| !isset($pObj->user['uid'])) {
			return;
		}
		// initialize service
		if (!$this->init()) {
			return;
		}
		// try auto login
		try {
			// retrive id
			$id = $pObj->user['tx_typogento_customer'];
			// retrive session
			$session = $this->getSession();
			// check login status
			if ($session->isLoggedIn()) {
				return;
			}
			// perform login
			if ($session->loginById($id)) {
				$session->renewSession();
			} else {
				throw new \Exception('Not found.', 1360877632);
			}
		} catch (\Exception $e) {
			// create exception
			$exception = new \Exception(
				sprintf('Single sign-on failed for user "%s" (%s): %s', $pObj->user['email'], $pObj->user['uid'], $e->getMessage()), 
				1357261148, $e
			);
			// log exception
			$this->logger->error($exception->getMessage());
		}
	}
	
	/**
	 * Performs auto logout for the Magento customer.
	 *
	 * @param array $params
	 * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $pObj
	 * @see $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing']
	 */
	public function logoffPreProcessing($params, \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $pObj) {
		// check logout status
		if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('logintype') != 'logout' 
			|| $pObj->loginType != 'FE') {
			return;
		}
		// initialize service
		if (!$this->init()) {
			return;
		}
		// try auto logout
		try {
			// retrive session
			$session = $this->getSession();
			// check login status
			if (!$session->isLoggedIn()) {
				return;
			}
			// perform logout
			$session->logout();
		} catch (\Exception $e) {
			// create exception
			$exception = new \Exception(
				sprintf('Single sign-off failed for user "%s" (%s): %s', $pObj->user['email'], $pObj->user['uid'], $e->getMessage()), 
				1357261148, $e
			);
			// log exception
			$this->logger->error($exception->getMessage());
		}
	}
	
	/**
	 * Prevents RSA authentication starting session before TypoGento.
	 *
	 * @param array $params
	 * @param \TYPO3\CMS\Felogin\Controller\FrontendLoginController $pObj
	 * @return array
	 * @see $TYPO3_CONF_VARS['EXTCONF']['felogin']['loginFormOnSubmitFuncs']
	 */
	public function loginFormHook($params, \TYPO3\CMS\Felogin\Controller\FrontendLoginController $pObj) {
		$this->init();
		return array(0 => '', 1 => '');
	}
	
	/**
	 * Gets the Magento customer.
	 * 
	 * @return \Mage_Customer_Model_Customer
	 */
	protected function getCustomer() {
		// reset customer
		$helper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Utility\\ConfigurationUtility');
		// get customer model
		$customer = \Mage::getSingleton('customer/customer');
		$customer->setWebsiteId($helper->getWebsiteId());
		return $customer;
	}
	
	/**
	 * Gets the TYPO3 frontend user wrapper.
	 * 
	 * @return \Typogento_Replication_Model_Typo3_Frontend_User
	 */
	protected function getFrontendUser() {
		// get frontend user model
		$user = \Mage::getModel('typogento_replication/typo3_frontend_user');
		return $user;
	}
	
	/**
	 * Gets the Magento customer session.
	 * 
	 * @return \Mage_Customer_Model_Session
	 */
	protected function getSession() {
		// get frontend user model
		$session = \Mage::getSingleton('customer/session');
		return $session;
	}
}
?>