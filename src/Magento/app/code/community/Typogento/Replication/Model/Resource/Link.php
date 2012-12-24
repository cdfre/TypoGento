<?php

/**
 * TypoGento replication link model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Replication_Model_Resource_Link extends Typogento_Core_Model_Resource_Typo3_Abstract {
	
	/**
	 * Constuctor
	 */
	protected function _construct() {
		$this->_init('typogento_replication/link', 'uid');
		$this->_resourcePrefix = 'typogento_replication';
	}
}

