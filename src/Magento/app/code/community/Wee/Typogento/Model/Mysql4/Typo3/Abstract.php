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
	protected function _getConnection($name) {
		
		if (isset($this->_connections[$name])) {
			return $this->_connections[$name];
		}
		$helper = Mage::helper('typogento/typo3');
		$config = Mage::getConfig()->getResourceConnectionConfig('typogento_'.$name);
		
		$config->host = $helper->getDatabaseHost();
		$config->username = $helper->getDatabaseUser();
		$config->password = $helper->getDatabasePassword();
		$config->dbname = $helper->getDatabaseName();
		$config->charset = $helper->getDatabaseCharset();
		
		$type = $this->_resources->getConnectionTypeInstance((string)$config->type);
		$this->_connections[$name] = $type->getConnection($config);
		
		return $this->_connections[$name];
	}
	

}

