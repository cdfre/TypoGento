<?php

/**
 * TypoGento TYPO3 frontend users model
 *
 * @deprecated
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Wee_Typogento_Model_Typo3_FeUsers extends Mage_Core_Model_Abstract {

	/**
	 * Class Constructor
	 *
	 */
	public function _construct() {
		$this->_init('typogento/typo3_feusers');
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

?>