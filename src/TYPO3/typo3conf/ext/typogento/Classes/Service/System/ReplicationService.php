<?php 

namespace Tx\Typogento\Service\Frontend\User;

use \Tx\Typogento\Utility\LogUtility;
use \Tx\Typogento\Core\Bootstrap;


/**
 * Frontend user provisioning service.
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class ReplicationService extends \TYPO3\CMS\Sv\AuthenticationService {

	
	/**
	 * Initialize service
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
			// register post user lookup hook
			if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'])) {
				$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'] = array();
			}
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][__CLASS__] =
				'Tx\\Typogento\\Service\\Frontend\\User\\ReplicationService->postUserLookUp';
		} catch (\Exception $e) {
			$this->errorPush(T3_ERR_SV_GENERAL, $e->getMessage());
			LogUtility::error('[Replication] '.$e->getMessage());
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
			$e = new Exception(sprintf('The data source "%s" is not supported.', $this->db_user['table']), 1357261473);
			$this->errorPush(T3_ERR_SV_GENERAL, $e->getMessage());
			LogUtility::error('[Replication] '.$e->getMessage());
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
		try {
			// get frontend user model
			$user = \Mage::getModel('typogento_replication/typo3_frontend_user');
			// load frontend user
			$user->load($this->login['uname'], 'email');
			// validate user
			if (!$user->getId()) {
				// discover possible customer
				$manager = \Mage::getSingleton('typogento_replication/manager');
				$customer = $manager->discover($user);
				// validate customer
				if (isset($customer) && $customer->getId()) {
					// replicate customer
					$manager->replicate($customer);
					//
					unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][__CLASS__]);
				}
			} else {
				return $user->getData();
			}
		} catch (\Exception $e) {
			LogUtility::error('[Replication] '.$e->getMessage());
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
		unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][__CLASS__]);
		// skip if login failed
		if ($pObj->loginFailure
			|| !$pObj->loginSessionStarted
			|| !isset($pObj->user['tx_typogento_customer'])) {
			return;
		}
		try {
			// init
			if (!$this->init()) {
				throw new Exception('Unknown error.', 1357261385);
			}
			// replicate frontend user
			$manager = \Mage::getSingleton('typogento_replication/manager');
			$user = \Mage::getModel('typogento_replication/typo3_frontend_user');
			$user->setData($pObj->user);
			$manager->replicate($user);
		} catch (\Exception $e) {
			LogUtility::error('[Replication] '.$e->getMessage());
		}
	}
}
?>