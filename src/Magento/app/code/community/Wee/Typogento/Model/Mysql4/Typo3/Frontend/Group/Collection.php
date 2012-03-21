<?php

/**
 * TypoGento frontend group collection
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

class Wee_Typogento_Model_Mysql4_Typo3_Frontend_Group_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {


	protected function _construct() {
		$this->_init('typogento/typo3_frontend_group');

	}


	public function toOptionArray() {
		return $this->_toOptionArray('uid', 'title');
	}

}