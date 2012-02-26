<?php

unset($MCONF);
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');

$BE_USER->modAccess($MCONF, 1);

require_once(t3lib_extmgm::extPath('wee_typogento').'lib/class.tx_weetypogento_div.php');
require_once(t3lib_extmgm::extPath('wee_typogento').'lib/class.tx_weetypogento_autoloader.php');

/**
 * TYPO3 Backend Module tx_weetypogento_modadmin
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

class tx_weetypogento_modadmin  extends t3lib_SCbase {
	
	/**
	 * Current user
	 * 
	 * @var Mage_Admin_Model_User
	 */
	protected $_user = null;
	
	/**
	 * Current session
	 *
	 * @var Mage_Admin_Model_Session
	 */
	protected $_session = null;

	/**
	 * The main method of the plugin
	 *
	 */
	public function main() {
		// force typo3 account data are set
		if (!isset($GLOBALS['BE_USER']->user)) {
			$this->_addMessage('access_denied_title', 'be_user_not_set_error', 
				t3lib_message_AbstractMessage::ERROR
			);
		}
		// force magento group membership for the typo3 account is set
		if (empty($GLOBALS['BE_USER']->user['tx_weetypogento_group'])) {
			$this->_addMessage('access_denied_title', 'account_membership_not_set_error', 
				t3lib_message_AbstractMessage::ERROR
			);
		}
		
		if ($this->_hasErrors()) { 
			return;
		}
		// 
		if (isset($this->_session) 
		&& $this->_session->isLoggedIn()) {
			// redirect to magento backend
			$this->_redirect();
		} else {
			// load the magento account
			$this->_load();
			// login with magento account
			$this->_login();
		}
	}
	
	/**
	 * Init the plugin
	 * 
	 */
	public function init() {
		// init template for messages if any
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];
		$this->doc->setModuleTemplate(t3lib_extMgm::extRelPath('wee_typogento') . 'mod_admin/info.html');
		// 
		try {
			// init the parent
			parent::init();
			// init magento resources
			t3lib_div::makeInstance('tx_weetypogento_autoloader', true);
			Mage::app('admin');
			// init magento admin session
			$this->_session = Mage::getSingleton('core/session', array('name' => 'adminhtml'));
			// init magento admin session
			$this->_session = Mage::getSingleton('admin/session');
		} catch(Exception $e) {
			// log error
			$this->_addMessage(
				'initalizing_failed_title', 
				$e->getMessage(), 
				t3lib_message_AbstractMessage::ERROR
			);
		}
	}
	
	/**
	 * Print content
	 * 
	 * Prints messages if redirect is not successfully.
	 */
	public function printContent() {
		$this->content  = $this->doc->startPage('TypoGento');
		$this->content .= $this->doc->moduleBody(array(), array(), 
				array('CONTENT' => t3lib_FlashMessageQueue::renderFlashMessages())
		);
		$this->content .= $this->doc->endPage();
		// echo the template
		echo $this->content;
	}
	
	/**
	 * Load account
	 *
	 * Loads the Magento account or creates if not exist.
	 */
	protected function _load() {
		try {
			// get typo3 account data
			$data = $GLOBALS['BE_USER']->user;
			// load magento account if this is not already done
			if (!isset($this->_user)) {
				// laod magento account data
				$this->_user = Mage::getSingleton('admin/user');
				$this->_user->loadByUsername($GLOBALS['BE_USER']->user['username']);
			}
			// finish if magento account has an id
			if ($this->_user->getId()) {
				return;
			}
			// copy typo3 account data otherwise
			$this->_user->setData(array('username' => $data['username'],
				'password' => $data['password'],
				'firstname' => $data['realName'],
				'lastname' => $data['realName'],
				'email' => $data['email'],
				'is_active' => true
			));
			// save new magento account
			$this->_user->save();
			// set account role id and save it
			$this->_user->setRoleIds(array($data['tx_weetypogento_group']));
			$this->_user->setRoleUserId($this->_user->getUserId());
			$this->_user->saveRelations();
		} catch(Exception $e) {
			// log error
			$this->_addMessage('access_failed_title', $e->getMessage(), 
				t3lib_message_AbstractMessage::ERROR
			);
		}
	}

	/**
	 * Login user
	 *
	 * @param string $username
	 */
	protected function _login() {
		try {
			// force accounts exist
			if (!isset($this->_user) 
			|| !$this->_user->getId()) {
				$this->_addMessage('login_failed_title', 'account_not_found_error', 
					t3lib_message_AbstractMessage::ERROR
				);
				return;
			}
			// force session exist
			if (!isset($this->_session)) {
				$this->_addMessage('login_failed_title', 'initializing_session_failed_error', 
					t3lib_message_AbstractMessage::ERROR
				);
				return;
			}
			// get typo3 account data
			$data = $GLOBALS['BE_USER']->user;
			// force account roles equal
			if ($this->_user->getRole()->getId() != $data['tx_weetypogento_group']) {
				$this->_addMessage(
					'access_denied_title', 
					'account_group_not_set_error', 
					t3lib_message_AbstractMessage::ERROR
				);
				return;
			}
			// force magento account is active
			if ($this->_user->getIsActive() != '1') {
				$this->_addMessage('access_denied_title', 'account_not_active_error', 
					t3lib_message_AbstractMessage::ERROR);
			}
			// don't get it ...
			if (!$this->_user->hasAssigned2Role($this->_user->getId())) {
				$this->_addMessage(
					'access_denied_title', 
					'account_role_not_set_error', 
					t3lib_message_AbstractMessage::ERROR
				);
			}
			// finish magento login
			Mage::dispatchEvent('admin_user_authenticate_after', array(
				'username' => $this->_user->getUsername(),
				'password' => $this->_user->getPassword(),
				'user' => $this->_user,
				'result' => true,
			));
			$this->_user->getRole();
			$this->_user->getResource()->recordLogin($this->_user);
			$this->_session->renewSession();
			// 
			if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
				Mage::getSingleton('adminhtml/url')->renewSecretUrls();
			}
			// set session data
			$this->_session->start();
			$this->_session->setIsFirstPageAfterLogin(true);
			$this->_session->setUser($this->_user);
			$this->_session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
			// redirect to magento backend
			$this->_redirect();
		} catch (Exception $e) {
			// clean user if set
			if (isset($this->_user)) {
				$this->_user->unsetData();
			}
			// clean up session if set
			if (isset($this->session)) {
				$this->session->unsAcl();
				$this->session->unsUser();
				$this->session->unsIsFirstPageAfterLogin();
			}
			// log error
			$this->_addMessage('login_failed_title', $e->getMessage(), 
				t3lib_message_AbstractMessage::ERROR
			);
		}
	}
	
	/**
	 * Redirect client
	 * 
	 * Redirects to the Magento backend.
	 */
	protected function _redirect() {
		try {
			// create redirect url
			$url = Mage::getSingleton('adminhtml/url');
			$redirect =  $url->addSessionParam()->getUrl('adminhtml/dashboard/*', 
				array('_current' => true)
			);
			Mage::dispatchEvent('admin_session_user_login_success', 
				array('user' => $this->_user)
			);
			// disable cookies for the first request
			header_remove('set-cookie');
			// send redirect
			header('Location: '.$redirect);
			exit;
		} catch(Exception $e) {
			// log error
			$this->_addMessage('redirect_failed_title', $e->getMessage(), 
				t3lib_message_AbstractMessage::ERROR
			);
		}
	}
	
	protected function _hasErrors() {
		// get all messages
		$messages = t3lib_FlashMessageQueue::getAllMessages();
		// check if any errors within
		foreach($messages as $message) {
			// return true and skip if an error is found
			if ($message->getSeverity() === t3lib_message_AbstractMessage::ERROR) {
				return true;
			}
		}
		// nothing found
		return false;
	}
	
	/**
	 * Add message helper
	 * 
	 * @param string $title
	 * @param string $message
	 * @param int $severity
	 */
	protected function _addMessage($title = null, $message = null, $severity = t3lib_message_AbstractMessage::OK){
		// skip if no message is set
		if (!isset($message)) {
			return;
		}
		// get current language and include resource
		$language = $GLOBALS['LANG'];
		$language->includeLLFile('EXT:wee_typogento/mod_admin/locallang.xml');
		// get title label
		$value = $language->getLL($title);
		if (!empty($value)) {
			$title = $value;
		}
		// get message label
		$value = $language->getLL($message);
		if (!empty($value)) {
			$message = $value;
		}
		// create flash message
		$message = t3lib_div::makeInstance('t3lib_FlashMessage', $message, $title, $severity);
		// add flash message to the queue
		t3lib_FlashMessageQueue::addMessage($message);
	}
}

/**
 * Create the instance
 */
$SOBE = t3lib_div::makeInstance('tx_weetypogento_modadmin');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/mod_admin/index.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/mod_admin/index.php']);
}

?>