<?php

/**
 * TypoGento frontend group collection
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

class Typogento_Replication_Model_Resource_Typo3_Frontend_Group_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {


	protected function _construct() {
		$this->_init('typogento_replication/typo3_frontend_group');

	}


	public function toOptionArray() {
		return $this->_toOptionArray('uid', 'title');
	}

}