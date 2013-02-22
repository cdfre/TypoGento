<?php

/**
 * TYPO3 frontend user helper
 *
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Replication_Helper_Typo3_Frontend_User extends Mage_Core_Helper_Abstract {
	
	const XML_PATH_PAGE_ID  = 'typo3/frontend/users_pid';
	const XML_PATH_GROUP_ID = 'typo3/frontend/group_uid';
	
	public function getPageId() {
		return Mage::getStoreConfig(self::XML_PATH_PAGE_ID, Mage::app()->getStore());
	}
	
	public function getGroupId() {
		return Mage::getStoreConfig(self::XML_PATH_GROUP_ID, Mage::app()->getStore());
	}
	
}