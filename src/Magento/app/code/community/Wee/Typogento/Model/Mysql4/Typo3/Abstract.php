<?php

/**
 * TypoGento TYPO3 persistent base model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Wee_Typogento_Model_Mysql4_Typo3_Abstract extends Mage_Core_Model_Mysql4_Abstract {
	
	
	/**
	 * Class Constructor
	 *
	 */
	protected function _construct() {
		parent::_construct();
	}
	
	
	/**
	 * Get connection by name or type
	 *
	 * @param   string $connectionName
	 * @return  Zend_Db_Adapter_Abstract
	 */
	protected function _getConnection($connectionName) {
		
		if (isset ( $this->_connections [$connectionName] )) {
			return $this->_connections [$connectionName];
		}
		
		$connConfig = Mage::getConfig ()->getResourceConnectionConfig ( 'typogento_' . $connectionName );
		
		foreach (array('host', 'username', 'password', 'dbname') as $field) {
			$connConfig->{$field} = (string) Mage::getStoreConfig('typogento/typo3_db/' . $field);
		}
		
		$typeInstance = $this->_resources->getConnectionTypeInstance ( ( string ) $connConfig->type );
		$this->_connections [$connectionName] = $typeInstance->getConnection ( $connConfig );
		
		return $this->_connections [$connectionName];
	}
	

}

