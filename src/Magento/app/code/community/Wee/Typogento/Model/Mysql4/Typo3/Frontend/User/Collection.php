<?php

/**
 * TypoGento frontend group collection
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

class Wee_Typogento_Model_Mysql4_Typo3_Frontend_User_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {


	protected function _construct() {
		$this->_init('typogento/typo3_frontend_user');

	}


	public function toOptionArray() {
		return $this->_toOptionArray('uid', 'name');
	}
	
	/**
	 * Init collection select
	 *
	 * @return Mage_Core_Model_Resource_Db_Collection_Abstract
	 */
	protected function _initSelect() {
		parent::_initSelect();
		$this->getSelect()
			->where('deleted = 0')
			->where('pid = ? ', Mage::getStoreConfig('typogento/typo3_fe/users_pid'));
		return $this;
	}

}