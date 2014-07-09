<?php 

namespace Tx\Typogento\Service\System;

use Tx\Typogento\Core\Bootstrap;


/**
 * Frontend user provisioning service.
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class ReplicationService extends \TYPO3\CMS\Sv\AuthenticationService {

	/**
	 * @var \TYPO3\CMS\Core\Log\LogManager
	 */
	protected $logger = null;
	
	/**
	 * Initializes service.
	 * 
	 * @return boolean
	 */
	public function init() {
		// skip if parent init failed
		if (!parent::init()) {
			return false;
		}
		// create logger
		$this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
		// init magento framework
		try {
			// init the autoloader
			Bootstrap::initialize();
			// init the application
			\Mage::app();
			// register post user lookup hook
			
		} catch (\Exception $e) {
			$this->errorPush(T3_ERR_SV_GENERAL, $e->getMessage());
			$this->logger->error($e->getMessage());
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
			// log the exception
			$exception = new \Exception(sprintf('The data source "%s" is not supported.', $this->db_user['table']), 1357261473);
			$this->logger->error($exception->getMessage());
			// add the error
			$this->errorPush(T3_ERR_SV_GENERAL, $exception->getMessage());
		}
	}

	/**
	 * Get frontend user
	 *
	 * Provides automatic synchronization with Magento customers.
	 *
	 * @return mixed The user data array or false
	 */
	public function getUser() {
		try {
			// get frontend user model
			$user = \Mage::getModel('typogento_replication/typo3_frontend_user');
			// retive frontend user email
			$email = $this->login['uname'];
			// load frontend user
			$user->load($email, 'email');
			// validate user
			if (!$user->getId()) {
				// discover possible customer
				$manager = \Mage::getSingleton('typogento_replication/manager');
				$customer = $manager->discover($user);
				// validate customer
				if (isset($customer) && $customer->getId()) {
					// replicate customer
					$manager->replicate($customer);
					// unregister hook
					$this->unregisterHook();
				}
			} else {
				// return user data
				return $user->getData();
			}
		} catch (\Exception $e) {
			$this->logger->error($e->getMessage());
		}
		// replication failed
		return false;
	}

	/**
	 * Update frontend user password after sucessful login.
	 *
	 * @param unknown_type $params
	 * @param unknown_type $pObj
	 */
	 public function postUserLookUp($params, &$pObj) {
		// unregister hook
		$this->unregisterHook();
		// skip if login failed
		if ($pObj->loginFailure
			|| !$pObj->loginSessionStarted
			|| !isset($pObj->user['tx_typogento_customer'])) {
			return;
		}
		try {
			// init
			if (!$this->init()) {
				throw new \Exception('Unknown error.', 1357261385);
			}
			// replicate frontend user
			$manager = \Mage::getSingleton('typogento_replication/manager');
			$user = \Mage::getModel('typogento_replication/typo3_frontend_user');
			$user->setData($pObj->user);
			$manager->replicate($user);
		} catch (\Exception $e) {
			$this->logger->error($e->getMessage());
		}
	}
	
	protected function registerHook() {
		// register post user lookup hook
		if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'])) {
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'] = array();
		}
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][__CLASS__] =
			'Tx\\Typogento\\Service\\System\\ReplicationService->postUserLookUp';
	}
	
	protected function unregisterHook() {
		// unregister hook
		unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][__CLASS__]);
	}
}
?>