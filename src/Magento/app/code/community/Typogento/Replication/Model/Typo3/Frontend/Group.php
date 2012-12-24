<?php

/**
 * TypoGento group model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Replication_Model_Typo3_Frontend_Group extends Mage_Core_Model_Abstract {

	/**
	 * Class Constructor
	 *
	 */
	public function _construct() {

		$this->_init('typogento_replication/typo3_frontend_group');
	}

	/**
	 * get fe_group by uid
	 *
	 * @param int $id
	 * @return array Group Data
	 */
	public function getGroupById($id) {
		return $this->_getResource()->getGroupById($id);
	}

}
