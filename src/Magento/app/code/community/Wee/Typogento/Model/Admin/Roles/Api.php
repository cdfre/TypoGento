<?php

/**
 * TypoGento admin roles API model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Wee_Typogento_Model_Admin_Roles_Api extends Mage_Api_Model_Resource_Abstract {
	
	/**
	 * Retrieve role list
	 *
	 * @return array
	 */
	public function items() {

		$rolesCollection = Mage::getModel("admin/roles")->getCollection()->load();

		$res = array();
		$additional['value'] = 'role_id';
		$additional['label'] = 'role_name';

		foreach ($rolesCollection as $item) {
			foreach ($additional as $code => $field) {
				$data[$code] = $item->getData($field);
			}
			$res[] = $data;
		}
		return $res;
	}
}