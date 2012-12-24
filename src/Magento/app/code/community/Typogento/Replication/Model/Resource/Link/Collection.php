<?php

/**
 * TypoGento frontend group collection
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

class Typogento_Replication_Model_Resource_Link_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {


	protected function _construct() {
		$this->_init('typogento_replication/link');
	}

}