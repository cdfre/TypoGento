<?php

/**
 * TypoGento user model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Wee_Typogento_Model_Mysql4_Typo3_Frontend_User extends Wee_Typogento_Model_Mysql4_Typo3_Abstract {
	
	/**
	 * Constuctor
	 *
	 */
	protected function _construct() {
		$this->_init ( 'typogento/typo3_frontend_user', 'uid' );
		$this->_resourcePrefix = 'typogento';
	}
	
	
	/**
	 * Get an TYPO3 fe_user
	 *
	 * @param   int unique ID
	 * @return  array
	 */
	public function getUserById($id) {
		$read = $this->_getReadAdapter ();
		$select = $read->select ();
		
		$select->from ( array ('main_table' => $this->getMainTable () ) )->where ( $this->getIdFieldName () . ' = ?', $id )->limit ( 1 );
		
		return $read->fetchRow ( $select );
	}
}

