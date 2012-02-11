<?php

/**
 * TypoGento store view API model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Wee_Typogento_Model_Store_Api extends Mage_Api_Model_Resource_Abstract
{
	/**
	 * Retrieve storeview list
	 *
	 * @return array
	 */
	public function items() {
		$storeviews = Mage::getModel('core/store')->getCollection();

		$res = array();
		$additional['value'] = 'code';
		$additional['label'] = 'name';

		foreach ($storeviews as $item) {
			foreach ($additional as $code => $field) {
				$data[$code] = $item->getData($field);
			}
			$res[] = $data;
		}
		return $res;
	}
}