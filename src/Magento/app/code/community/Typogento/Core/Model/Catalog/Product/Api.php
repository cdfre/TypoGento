<?php

/**
 * Typogento catalog product API model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Core_Model_Catalog_Product_Api extends Mage_Catalog_Model_Api_Resource {


	/**
	 * Retrieve product urlKeys
	 *
	 * @param string|int $store
	 * @return array
	 */
	public function urlkeys($store = null) {
		$collection = Mage::getModel('catalog/product')->getCollection()
			->setStoreId($this->_getStoreId($store))
			->addAttributeToSelect('id')
			->addAttributeToSelect('url_key')
			->load();

		$collectionArray = array();

		foreach ($collection as $product) {
			$collectionArray[$product->getId()]= $product->getUrlKey();
		}
		 
		return $collectionArray;
	}
}