<?php

/**
 * TypoGento replication API
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Replication_Model_Api extends Mage_Api_Model_Resource_Abstract {
	
	/**
	 * 
	 */
	public function providers() {
		// get replication manager
		$manager = Mage::getSingleton('typogento_replication/manager');
		$providers = array();
		// provider ids
		$ids = array_keys($manager->getProviders());
		// collect providers
		foreach ($ids as $id) {
			$providers[] = array('id' => $id, 'display' => Mage::helper('typogento_replication')->__($id));
		}
		// return result
		return $providers;
	}
	
	/**
	 * Retrieve replication sources
	 *
	 * @param string $provider The provider id
	 * @return array The sources of the given provider or null
	 */
	public function sources($provider) {
		// get replication manager
		$manager = Mage::getSingleton('typogento_replication/manager');
		$sources = array();
		// get source provider
		$provider = $manager->getProvider($provider);
		// validate provider
		if ($provider instanceof Typogento_Replication_Model_Provider_Abstract) {
			$collection = $provider->getCollection();
			// collect sources
			foreach ($collection as $source) {
				$sources[] = array('id' => $source->getId(), 'display' => $provider->getDisplay($source));
			}
		}
		// return result
		return $sources;
	}
	
	/**
	 * Retrieve replication targets
	 *
	 * @param string $provider The provider id
	 * @return array The target of the given provider or null
	 */
	public function targets($provider) {
		// get replication manager
		$manager = Mage::getSingleton('typogento_replication/manager');
		$sources = array();
		// get source provider
		$provider = $manager->getProvider($provider);
		// validate source provider
		if ($provider instanceof Typogento_Replication_Model_Provider_Abstract) {
			// get target model
			$model = $provider->getModel(false);
			// get target provider
			$provider = $manager->getProvider($model);
			// validate target provider
			if ($provider instanceof Typogento_Replication_Model_Provider_Abstract) {
				$collection = $provider->getCollection();
				// collect targets
				foreach ($collection as $target) {
					$sources[] = array('id' => $target->getId(), 'display' => $provider->getDisplay($target));
				}
			}
		}
		// return result
		return $sources;
	}
}
