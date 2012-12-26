<?php 

namespace Tx\Typogento\Controller;

use Tx\Typogento\Core\Bootstrap;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Messaging\FlashMessage;
use \TYPO3\CMS\Core\Messaging\FlashMessageQueue;

/**
 * Administration controller
 * 
 * @namespace Tx\Typogento\Controller
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class AdministrationController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
	
	/**
	 * @var string Key of the extension this controller belongs to
	 */
	protected $extensionName = 'typogento';
	
	/**
	 * Current user
	 *
	 * @var Mage_Admin_Model_User
	 */
	protected $user = null;
	
	/**
	 * Current session
	 *
	 * @var Mage_Admin_Model_Session
	 */
	protected $session = null;
	
	/**
	 * Loads the Magento account or creates one if necessary.
	 * 
	 * @todo This is safety-critical and must be documented and maybe redesigned.
	 * @return void
	 */
	protected function load() {
		try {
			// get typo3 account data
			$data = $GLOBALS['BE_USER']->user;
			// load magento account if this is not already done
			if (!isset($this->user)) {
				// load magento account data
				$this->user = \Mage::getSingleton('admin/user');
				$this->user->loadByUsername($GLOBALS['BE_USER']->user['username']);
			}
			// finish if magento account has an id
			if ($this->user->getId()) {
				return;
			}
			// copy typo3 account data otherwise
			$this->user->setData(array(
				'username' => $data['username'],
				'password' => $data['password'],
				'firstname' => $data['realName'],
				'lastname' => $data['realName'],
				'email' => $data['email'],
				'is_active' => true
			));
			// save new magento account
			$this->user->save();
			// set account role id and save it
			$this->user->setRoleIds(array($data['tx_typogento_group']));
			$this->user->setRoleUserId($this->user->getUserId());
			$this->user->saveRelations();
		} catch(\Exception $e) {
			// log error
			$this->log(
				'mod1_access_failed_title', 
				$e->getMessage(),
				FlashMessage::ERROR
			);
		}
	}
	
	/**
	 * Performs auto login for the user.
	 * 
	 * @return void
	 */
	protected function login() {
		try {
			// force accounts exist
			if (!isset($this->user) || !$this->user->getId()) {
				$this->log(
					'mod1_login_failed_title', 
					'mod1_account_not_found_error',
					FlashMessage::ERROR
				);
				return;
			}
			// force session exist
			if (!isset($this->session)) {
				$this->log(
					'mod1_login_failed_title', 
					'mod1_initializing_session_failed_error',
					FlashMessage::ERROR
				);
				return;
			}
			// get typo3 account data
			$data = $GLOBALS['BE_USER']->user;
			// force account roles equal
			if ($this->user->getRole()->getId() != $data['tx_typogento_group']) {
				$this->log(
					'mod1_access_denied_title',
					'mod1_account_group_not_set_error',
					FlashMessage::ERROR
				);
				return;
			}
			// force magento account is active
			if ($this->user->getIsActive() != '1') {
				$this->log(
					'mod1_access_denied_title', 
					'mod1_account_not_active_error',
					FlashMessage::ERROR);
			}
			// force magento account assigned to a role
			if (!$this->user->hasAssigned2Role($this->user->getId())) {
				$this->log(
					'mod1_access_denied_title',
					'mod1_account_role_not_set_error',
					FlashMessage::ERROR
				);
			}
			// finish magento login
			\Mage::dispatchEvent('admin_user_authenticate_after', array(
				'username' => $this->user->getUsername(),
				'password' => $this->user->getPassword(),
				'user' => $this->user,
				'result' => true
			));
			$this->user->getRole();
			$this->user->getResource()->recordLogin($this->user);
			$this->session->renewSession();
			//
			if (\Mage::getSingleton('adminhtml/url')->useSecretKey()) {
				\Mage::getSingleton('adminhtml/url')->renewSecretUrls();
			}
			// set session data
			$this->session->start();
			$this->session->setIsFirstPageAfterLogin(true);
			$this->session->setUser($this->user);
			$this->session->setAcl(\Mage::getResourceModel('admin/acl')->loadAcl());
			// redirect to magento backend
			$this->redirect();
		} catch(\Exception $e) {
			// clean user if set
			if (isset($this->user)) {
				$this->user->unsetData();
			}
			// clean up session if set
			if (isset($this->session)) {
				$this->session->unsAcl();
				$this->session->unsUser();
				$this->session->unsIsFirstPageAfterLogin();
			}
			// log error
			$this->log(
				'mod1_login_failed_title', 
				$e->getMessage(),
				FlashMessage::ERROR
			);
		}
	}
	
	/**
	 * Redirects the client.
	 * 
	 * @return void
	 */
	protected function redirect() {
		try {
			// create redirect url
			$url = \Mage::getSingleton('adminhtml/url');
			$redirect =  $url->addSessionParam()->getUrl('adminhtml/dashboard/*',
				array('_current' => true)
			);
			\Mage::dispatchEvent('admin_session_user_login_success',
				array('user' => $this->user)
			);
			// disable cookies for the first request
			header_remove('set-cookie');
			// send redirect
			$this->response->setHeader('Location', $redirect);
			$this->response->sendHeaders();
		} catch(\Exception $e) {
			// log error
			$this->log(
				'mod1_redirect_failed_title', 
				$e->getMessage(),
				FlashMessage::ERROR
			);
		}
	}
	
	/**
	 *
	 * @return boolean
	 */
	protected function hasErrors() {
		// get all messages
		$messages = FlashMessageQueue::getAllMessages();
		// check if any errors within
		foreach($messages as $message) {
			// return true and skip if an error is found
			if ($message->getSeverity() === FlashMessage::ERROR) {
				return true;
			}
		}
		// nothing found
		return false;
	}
	
	/**
	 * Logs a message.
	 *
	 * @param string $title
	 * @param string $message
	 * @param int $severity
	 * @return void
	 */
	protected function log($title = null, $message = null, $severity = FlashMessage::OK){
		// skip if no message is set
		if (!isset($message)) {
			return;
		}
		// translate title
		$title = \Tx\Typogento\Utility\LocalizationUtility::translate($title);
		// translate message
		$message = \Tx\Typogento\Utility\LocalizationUtility::translate($message);
		// create flash message
		$message = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\\FlashMessage', $message, $title, $severity);
		// add flash message to the queue
		FlashMessageQueue::addMessage($message);
	}
	
	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * @return void
	 */
	protected function initializeAction() {
		try {
			// init magento framework
			Bootstrap::initialize();
			// emulate index.php entry point for correct urls generation
			\Mage::register('custom_entry_point', true);
			// init magento admin application
			\Mage::init('admin');
			// init magento admin session
			$this->session = \Mage::getSingleton('core/session', array('name' => 'adminhtml'));
			// init magento admin session
			$this->session = \Mage::getSingleton('admin/session');
		} catch(\Exception $e) {
			// log error
			$this->log(
				'mod1_initalizing_failed_title',
				$e->getMessage(),
				FlashMessage::ERROR
			);
		}
	}
	
	/**
	 * Opens the backend or displays an error message on failure.
	 * 
	 * @return void
	 */
	public function indexAction() {
		// force typo3 account data are set
		if (!isset($GLOBALS['BE_USER']->user)) {
			$this->log(
				'mod1_access_denied_title', 
				'mod1_be_user_not_set_error',
				FlashMessage::ERROR
			);
		}
		// force magento group membership for the typo3 account is set
		if (empty($GLOBALS['BE_USER']->user['tx_typogento_group'])) {
			$this->log(
				'mod1_access_denied_title', 
				'mod1_account_membership_not_set_error',
				FlashMessage::ERROR
			);
		}
		
		if ($this->hasErrors()) {
			return;
		}
		//
		if (isset($this->session) && $this->session->isLoggedIn()) {
			// redirect to magento backend
			$this->redirect();
		} else {
			// load the magento account
			$this->load();
			// login with magento account
			$this->login();
		}
	}
}
?>