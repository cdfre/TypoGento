<?php

/**
 * TypoGento frontend user model
 *
 * Provides direct access to the TYPO3 fe_users through Magento.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Replication_Model_Typo3_Frontend_User extends Mage_Core_Model_Abstract {
	
	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$this->_init('typogento_replication/typo3_frontend_user');
	}
	
	public function isEnabled() {
		return (
			$this->getData('disabled') == 0 
			&& ($this->getData('starttime') == 0 
				|| $this->getData('starttime') > time())
			&& ($this->getData('endtime') == 0 
				|| $this->getData('endtime') < time())
		);
	}
	
	public function findEmailDuplicates() {
		return $this->_getResource()->findEmailDuplicates();
	}
	
	public function findCustomerDuplicates() {
		return $this->_getResource()->findCustomerDuplicates();
	}
}
