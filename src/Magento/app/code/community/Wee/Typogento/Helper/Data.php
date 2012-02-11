<?php

/**
 * TypoGento data helper
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Wee_Typogento_Helper_Data extends Mage_Core_Helper_Abstract {
	
	const TYPO3_BE_BASE_URL = 'typogento/typo3_be/base_url';
	
	/**
	 * returns Config Data
	 *
	 * @param string $field
	 * @return array config
	 */
	public function getConfigData($field) {
		$path = 'typogento/config/'.$field;
		$config = Mage::getStoreConfig($path, Mage::app()->getStore());
		return $config;
	}
}
