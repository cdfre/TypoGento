<?php

/**
 * TypoGento group model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Wee_Typogento_Model_Typo3_Replication_Link extends Mage_Core_Model_Abstract {

	/**
	 * Constructor
	 */
	public function _construct() {

		$this->_init('typogento/typo3_replication_link', 'uid');
	}
}
