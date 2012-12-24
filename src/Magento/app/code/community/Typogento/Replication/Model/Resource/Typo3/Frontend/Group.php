<?php

/**
 * TypoGento group model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Replication_Model_Resource_Typo3_Frontend_Group extends Typogento_Core_Model_Resource_Typo3_Abstract {
	
	/**
	 * Constuctor
	 *
	 */
	protected function _construct() {
		$this->_init('typogento_replication/typo3_frontend_group', 'uid' );
		$this->_resourcePrefix = 'typogento_replication';
	}
	
	
	/**
	 * Get an TYPO3 fe_group
	 *
	 * @param   int unique ID
	 * @return  array
	 */
	public function getGroupById($id) {
		$read = $this->_getReadAdapter ();
		$select = $read->select ();
		
		$select->from ( array ('main_table' => $this->getMainTable () ) )->where ( $this->getIdFieldName () . ' = ?', $id )->limit ( 1 );
		
		return $read->fetchRow ( $select );
	}
}

