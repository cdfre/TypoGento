<?php

/**
 * TypoGento group model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Core_Model_Mysql4_Typo3_Replication_Link extends Typogento_Core_Model_Mysql4_Typo3_Abstract {
	
	/**
	 * Constuctor
	 *
	 */
	protected function _construct() {
		$this->_init('typogento/typo3_replication_link', 'uid');
		$this->_resourcePrefix = 'typogento';
	}
}

