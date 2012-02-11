<?php

/**
 * TypoGento catalog category API model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Wee_Typogento_Model_Catalog_Category_Api extends Mage_Catalog_Model_Api_Resource {
	
	/**
	 * Retrieve category urlKeys
	 *
	 * @param string|int $store
	 * @return array
	 */
	public function urlkeys($store = null) {
		$collection = Mage::getModel('catalog/category')->getCollection()
			->setStoreId($this->_getStoreId($store))
			->addAttributeToSelect('id')
			->addAttributeToSelect('url_key')->load();
		
		$collectionArray = array();
		
		foreach ($collection as $category) {
			$collectionArray[$category->getId()]= $category->getUrlKey();
		}
		 
		return $collectionArray;
	}
}
