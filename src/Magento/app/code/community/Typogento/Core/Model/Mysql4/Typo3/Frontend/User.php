<?php

/**
 * TypoGento user model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Core_Model_Mysql4_Typo3_Frontend_User extends Typogento_Core_Model_Mysql4_Typo3_Abstract {
	
	/**
	 * Constuctor
	 *
	 */
	protected function _construct() {
		$this->_init('typogento/typo3_frontend_user', 'uid');
		$this->_resourcePrefix = 'typogento';
	}
	
	protected function _getLoadSelect($field, $value, $object) {
		
		$field  = $this->_getReadAdapter()->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), $field));
		
		$select = $this->_getReadAdapter()->select()
			->from($this->getMainTable())
			->where($field . ' = ?', $value)
			->where('deleted = 0')
			->where('pid = ? ', Mage::helper('typogento/typo3')->getFrontendUsersPageId());
		
		return $select;
	}
	
	/**
	 * Check customer by id
	 *
	 * @param int $customerId
	 * @return bool
	 */
	public function checkFrontendUserId($frontendUserId) {
		
		$adapter = $this->_getReadAdapter();
		
		$bind    = array(
			'uid' => (int)$frontendUserId,
			'pid' => Mage::helper('typogento/typo3')->getFrontendUsersPageId()
		);
		
		$select  = $adapter->select()
			->from($this->getTable('customer/entity'), 'entity_id')
			->where('deleted = 0')
			->where('pid = :pid')
			->where('uid = :uid')
			->limit(1);
	
		$result = $adapter->fetchOne($select, $bind);
		
		if ($result) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check whether there are email duplicates of TYPO3 frontend users in global scope
	 *
	 * @return bool
	 */
	public function findEmailDuplicates() {
		
		$read = $this->_getReadAdapter();
		
		$select  = $read->select()
			->from(array('main_table' => $this->getMainTable()), array('email', 'cnt' => 'COUNT(*)'))
			->where('deleted = 0')
			->where('pid = ? ', Mage::helper('typogento/typo3')->getFrontendUsersPageId())
			->group('email')
			->order('cnt DESC')
			->limit(1);
		
		$lookup = $read->fetchRow($select);
		
		if (empty($lookup)) {
			return false;
		}
		
		return $lookup['cnt'] > 1;
	}
	
	/**
	 * Check whether there are email duplicates of TYPO3 frontend users in global scope
	 *
	 * @return bool
	 */
	public function findCustomerDuplicates() {
		
		$read = $this->_getReadAdapter();
		
		$select  = $read->select()
			->from(array('main_table' => $this->getMainTable()), array('tx_typogento_customer', 'cnt' => 'COUNT(*)'))
			->where('deleted = 0')
			->where('pid = ? ', Mage::helper('typogento/typo3')->getFrontendUsersPageId())
			->where('tx_typogento_customer <> 0')
			->group('tx_typogento_customer')
			->order('cnt DESC')
			->limit(1);
		
		$lookup = $read->fetchRow($select);
		
		if (empty($lookup)) {
			return false;
		}
		
		return $lookup['cnt'] > 1;
	}
}

