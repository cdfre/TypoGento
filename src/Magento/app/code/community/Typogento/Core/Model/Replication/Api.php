<?php

/**
 * TypoGento replication API
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Core_Model_Replication_Api extends Mage_Api_Model_Resource_Abstract {
	
	/**
	 * 
	 */
	public function providers() {
		
	}
	
	/**
	 * Retrieve replication sources
	 *
	 * @param int $provider The provider id
	 * @return array The sources of the given provider or null
	 */
	public function sources($provider) {
		// get replication manager
		$manager = Mage::getSingleton('typogento/replication_manager');
		$sources = array();
		$provider = $manager->getProviderById($provider);
		
		
		if ($provider instanceof Typogento_Core_Model_Replication_Provider_Abstract) {
			$collection = $provider->getCollection();
			
			foreach ($collection as $source) {
				$sources[] = array('id' => $source->getId(), 'display' => $provider->getDisplay($source));
			}
		}
		
		return $sources;
	}
	
	/**
	 * Retrieve replication targets
	 *
	 * @param int $provider The provider id
	 * @return array The target of the given provider or null
	 */
	public function targets($provider) {
		// get replication manager
		$manager = Mage::getSingleton('typogento/replication_manager');
		$sources = array();
		$provider = $manager->getProviderById($provider);
		
		if ($provider instanceof Typogento_Core_Model_Replication_Provider_Abstract) {
			$model = $provider->getModel(false);
			$provider = $manager->getProviderByObject($model);
		
			if ($provider instanceof Typogento_Core_Model_Replication_Provider_Abstract) {
				$collection = $provider->getCollection();
				
				foreach ($collection as $target) {
					$sources[] = array('id' => $target->getId(), 'display' => $provider->getDisplay($target));
				}
			}
		}
	
		return $sources;
	}
}
