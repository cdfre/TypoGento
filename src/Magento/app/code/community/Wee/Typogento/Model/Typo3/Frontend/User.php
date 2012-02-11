<?php

/**
 * TypoGento user model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Wee_Typogento_Model_Typo3_Frontend_User extends Mage_Core_Model_Abstract {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		$this->_init('typogento/typo3_frontend_user');
	}

	/**
	 * get fe_user by uid
	 *
	 * @param int $id
	 * @return array User Data
	 */
	public function getUserById($id) {
		return $this->_getResource()->getUserById($id);
	}

}
