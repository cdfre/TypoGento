<?php

require_once(t3lib_extmgm::extPath('sv').'class.tx_sv_auth.php');

/**
 * TypoGento frontend authentication service
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_weetypogento_auth_sv1 extends tx_sv_auth {

	protected $_mageCustomer = null;
	
	protected $_feUserCache = array();
	
	/**
	 * 
	 * @var tx_weetypogento_magentoHelper
	 */
	protected $_helper = null;
	
	public function __construct() {
		$this->_helper = t3lib_div::makeInstance('tx_weetypogento_magentoHelper');
	}
	
	/**
	 * Authenticate a user
	 * 
	 * Loads the frontend session before user athentication.
	 * 
	 * @param array Data of user.
	 * @return boolean
	 */
	public function authUser($user) {
		if (empty($user['tx_weetypogento_id'])){
			return 100;
		}
		// get an Magento Instance
		$this->mage = t3lib_div::makeInstance('tx_weetypogento_autoloader');
		//
		Mage::app();
		// load the frontend session
		Mage::getSingleton('core/session', array('name' => 'frontend'));
		
		$customer = Mage::getSingleton('customer/customer')->setWebsiteId($this->_helper->getWebsiteId())->load($user['tx_weetypogento_id']);
	
		if ($customer->getConfirmation() && $customer->isConfirmationRequired()
		|| !$customer->validatePassword($this->login['uident'])
		|| $customer->getId() != $user['tx_weetypogento_id']) {
			return 100;
		}
	
		try {
			Mage::getSingleton('customer/session')->login($this->login['uname'], $this->login['uident']);
		} catch(Exeption $e) {
			return 100;
		}
	
		return 200;
	}
	
	/**
	 * Magento single sign on
	 *
	 * Fix the second case when Magento customer exists but TYPO3 
	 * frontend user does not.
	 * 
	 * @return mixed user array or false
	 */
	public function getUser() {
		// get an Magento Instance
		$this->mage = t3lib_div::makeInstance('tx_weetypogento_autoloader');
		Mage::app();
		// get Magento Customer
		$this->_mageCustomer = Mage::getSingleton('customer/customer')->setWebsiteId($this->_helper->getWebsiteId());
		$this->getMageCustomer()->loadByEmail($this->login['uname']);
		$this->getMageCustomer()->getAttributes();

		switch (true) {
			// Magento Customer and TYPO3 Frontend User already exist
			case $this->getMageCustomer()->getData('typo3_uid')
				&& $this->_loadUserByFieldValue('uid', $this->getMageCustomer()->getData('typo3_uid')):

				$uid = $this->_createOrUpdateFrontendUser($this->getMageCustomer()->getData('typo3_uid'));

				return $this->_loadUserByFieldValue('uid', $uid);
				break;

			// Magento Customer exists but TYPO Frontend User does not
			case $this->getMageCustomer()->getId()
				&& $this->_loadUserByFieldValue('username', $this->login['uname']) === null:

				$uid = $this->_createOrUpdateFrontendUser();

				$this->getMageCustomer()->setData('typo3_uid', $uid);
				$this->getMageCustomer()->getResource()->saveAttribute($this->getMageCustomer(), 'typo3_uid');

				// using $uid instead of a method of the non existing object in $feUsers
				return $this->_loadUserByFieldValue('uid', $uid );
				break;

			// Magento Customer and TYPO3 User exist but with no link
			case $this->getMageCustomer()->getId()
				&& ($feUser = $this->_loadUserByFieldValue('username', $this->login['uname'])):

				$uid = $this->_createOrUpdateFrontendUser($feUser['uid']);

				$this->getMageCustomer()->setData( 'typo3_uid', $uid );
				$this->getMageCustomer()->getResource()->saveAttribute( $this->getMageCustomer(), 'typo3_uid' );

				return $this->_loadUserByFieldValue('uid', $uid );
				break;

			// Magento Customer does not exist but TYPO3 Frontend User
			case !$this->getMageCustomer()->getId() && $this->_loadUserByFieldValue('username', $this->login['uname']):

				$feUser = $this->_loadUserByFieldValue('username', $this->login['uname']);

				$fields = array(
					'email' => $feUser['username'],
					'lastname' => $feUser['name'],
					'firstname' => $feUser['firstname'],
					'password'	=> $feUser['password'],
					'typo3_uid' => $feUser['uid'],
					'group_id' => $this->getMageCustomer()->getGroupId()
				);

				$this->getMageCustomer()->setData($fields)->save();
				$feUser['tx_weetypogento_id'] = $this->getMageCustomer()->getId();
				$this->_createOrUpdateFrontendUser($feUser['uid']);

				return $feUser;
				break;
		}
		return false;
	}
	
	
	/**
	* Get Magento customer
	*
	* @return Mage_Customer_Model_Customer
	*/
	protected function getMageCustomer() {
	
		return $this->_mageCustomer;
	}
	
	/**
	 * Load a TYPO3 frontend user by field and value
	 *
	 * @param unknown_type $field
	 * @param unknown_type $value
	 */
	protected function _loadUserByFieldValue($field, $value) {
	
		if (empty($_feUserCache[$field][$value])) {
	
			$_feUserCache[$field][$value] = null;
	
			$dbres = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'*',
			$this->db_user['table'],
							'pid='.intval( Mage::getStoreConfig('typogento/typo3_fe/users_pid')).
			($this->db_user['checkPidList'] ? ' AND pid IN ('.$GLOBALS['TYPO3_DB']->cleanIntList($this->db_user['checkPidList']).')' : '').
								' AND '.$field.' = "'.$GLOBALS['TYPO3_DB']->quoteStr($value, $this->db_user['table']).'"'.
			$this->db_user['enable_clause']
			);
	
			if ($dbres && $GLOBALS['TYPO3_DB']->sql_num_rows($dbres)) {
				$_feUserCache[$field][$value] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbres);
				$GLOBALS['TYPO3_DB']->sql_free_result($dbres);
			}
		}
	
		return $_feUserCache[$field][$value];
	}
	
	/**
	 * Create or update a TYPO3 frontend user
	 *
	 * @param int $id
	 */
	protected function _createOrUpdateFrontendUser($id = null) {
		// using 'Flagbit/Typo3connect/Typo3/Frontend/User' instead of 'Flagbit/Typo3connect/Typo3/FeUsers'
		$users = Mage::getSingleton ('typogento/typo3_frontend_user');

		if ($id != null) {
			$users->load($id);
		}
	
		$fields = array (
			'username' => $this->getMageCustomer()->getData('email'),
			'name' => $this->getMageCustomer()->getData('lastname'),
			'firstname' => $this->getMageCustomer()->getData('firstname'),
			'email' => $this->getMageCustomer()->getData('email'),
			'password' => $this->getMageCustomer()->getData('password_hash'),
			'pid' => Mage::getStoreConfig('typogento/typo3_fe/users_pid'),
			'usergroup' => $this->getMageCustomer()->getData('typo3_group_id') ? $this->getMageCustomer()->getData('typo3_group_id') :  Mage::getStoreConfig('typogento/typo3_fe/group_uid'),
			'tx_weetypogento_id' => $this->getMageCustomer()->getId()
		);
		$users->addData($fields);
		$users->save();
	
		return $users->getId();
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_auth_sv1.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_auth_sv1.php']);
}

?>
