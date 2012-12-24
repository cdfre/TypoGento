<?php

/**
 * TypoGento replication link model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Replication_Model_Link extends Mage_Core_Model_Abstract {

	/**
	 * Constructor
	 */
	public function _construct() {

		$this->_init('typogento_replication/link', 'uid');
	}
}
