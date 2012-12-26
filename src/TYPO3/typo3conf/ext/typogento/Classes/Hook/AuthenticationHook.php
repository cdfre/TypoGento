<?php 

namespace Tx\Typogento\Hook;

use \Tx\Typogento\Core\Bootstrap;

/**
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class AuthenticationHook {
	
	/**
	 * Logout customer session
	 *
	 * @param array $params
	 * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $authentication
	 */
	public function logoffPreProcessing($params, \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $authentication) {
		// skip if not logout
		if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('logintype') != 'logout'
			|| $authentication->loginType != 'FE') {
			return;
		}
		// init magento
		Bootstrap::initialize();
		//
		\Mage::app();
		// load the frontend session
		\Mage::getSingleton('core/session', array('name' => 'frontend'));
		// get session
		$session = \Mage::getModel('customer/session');
		// logout if session is logged in
		$session->logout();
	}
}
?>