<?php

/**
 * TypoGento catalog category API model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Wee_Typogento_Model_Replication_Api extends Mage_Api_Model_Resource_Abstract {
	
	public function providers() {
		
	}
	/**
	 * Retrieve category urlKeys
	 *
	 * @param string|int $store
	 * @return array
	 */
	public function sources($provider) {
		// get replication manager
		$manager = Mage::getSingleton('typogento/replication_manager');
		$sources = array();
		$provider = $manager->getProviderById($provider);
		
		
		if ($provider instanceof Wee_Typogento_Model_Replication_Provider_Abstract) {
			$collection = $provider->getCollection();
			
			foreach ($collection as $source) {
				$sources[] = array('id' => $source->getId(), 'display' => $provider->getDisplay($source));
			}
		}
		
		return $sources;
	}
	
	/**
	 * Retrieve category urlKeys
	 *
	 * @param string|int $store
	 * @return array
	 */
	public function targets($provider) {
		// get replication manager
		$manager = Mage::getSingleton('typogento/replication_manager');
		$provider = $manager->getProviderById($provider);
		$model = $provider->getModel(false);
		$provider = $manager->getProviderByObject($model);
		$sources = array();
	
		if ($provider instanceof Wee_Typogento_Model_Replication_Provider_Abstract) {
			$collection = $provider->getCollection();
			
			foreach ($collection as $target) {
				$sources[] = array('id' => $target->getId(), 'display' => $provider->getDisplay($target));
			}
		}
	
		return $sources;
	}
}
