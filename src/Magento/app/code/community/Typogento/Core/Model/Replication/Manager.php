<?php 

class Typogento_Core_Model_Replication_Manager {
	
	protected $_providers = array();
	
	protected $_processed = array();
	
	protected $_transaction = null;
	
	public function __construct() {
		Mage::dispatchEvent('typogento_replication_manager_initialize',
			array('manager' => $this)
		);
	}
	
	public function registerProvider(Typogento_Core_Model_Replication_Provider_Abstract $provider) {
		// get source model
		$model = $provider->getModel();
		$class = get_class($model);
		$id = intval($provider->getId());
		
		if (isset($this->_providers[$id]) || isset($this->_providers[$class])) {
			throw new Exception(Mage::helper('typogento')->__('Provider already exist.'));
		}
		
		$this->_providers[$class] = $provider;
		$this->_providers[strval($id)] = $provider;
	}
	
	public function getProviderById($id) {
		if (!isset($this->_providers[$id])) {
			return null;
		}
		
		return $this->_providers[$id];
	}
	
	public function getProviderByObject(Mage_Core_Model_Abstract $object) {
		if (!$this->_hasProvider($object)) {
			return null;
		}
	
		return $this->_getProvider($object);
	}
	
	public function discover(Mage_Core_Model_Abstract $source) {
		// validate typo3 resource setup
		if (!Mage::helper('typogento/typo3')->validateDatabaseConnection()
			|| !$this->_hasProvider($source)) {
			return null;
		}
		// get provider
		$provider = $this->_getProvider($source);
		// discover replica
		try {
			return $provider->discover($source);
		} catch (Exception $e) {
			Mage::logException($e);
		}
		// 
		return null;
	}
	
	/**
	 * 
	 * @see Typogento_Core_Model_Replication_Provider_Abstract
	 * @param Mage_Core_Model_Abstract $object
	 * @param unknown_type $segment
	 */
	public function replicate(Mage_Core_Model_Abstract $source) {
		// validate database connection and provider
		if (!Mage::helper('typogento/typo3')->validateDatabaseConnection()
			|| !$this->_hasProvider($source) || isset($this->_transaction)) {
			return $this;
		}
		// return if source already processed
		if ($this->_isProcessed($source)) {
			return null;
		}
		//
		try {
			// set source as processed
			$this->_addProcessed($source);
			//
			$this->_transaction = Mage::getModel('core/resource_transaction');
			//
			$target = $this->_replicate($source);
			$this->_replicate($target, $source);
			//
			$this->_transaction->save();
		} catch (RuntimeException $e) {
			// force atomic replications
		} catch (Execption $e) {
			Mage::logException($e);
		}
		
		$this->_transaction = null;
		
		return $this;
	}
	
	protected function _replicate(Mage_Core_Model_Abstract $source, Mage_Core_Model_Abstract $target = null) {
		// validate replication source
		if (!$source->getId()) {
			throw new Exception(Mage::helper('typogento')->__('Replication source has no id.'));
		}
		// validate replication source
		if ($source->hasChanges()) {
			throw new Exception(Mage::helper('typogento')->__('Replication source has pending changes.'));
		}
		// get provider
		$provider = $this->_getProvider($source);
		// get replication link
		$link = $this->_getLink($source);
		// validate replication links number
		if (!$link->getId()) {
			// discover replica
			if ($target === null) {
				$target = $provider->discover($source);
			}
			if (!$target->getId()) {
				// create new replica
				$this->_create($source, $target, $provider);
				// validate replica
				if ($target->getId()) {
					// create replication link
					$link = $this->_createLink($source, $target);
					// catch link
					$this->_transaction->addObject($link);
				}
			} else {
				// create replication link
				$link = $this->_createLink($source, $target);
				// update replica
				$this->_update($source, $target, $link, true);
				// update replication link
				$link->setData('tstamp', time());
				// catch link
				$this->_transaction->addObject($link);
			}
		} else {
			// discover target
			$target = $link->getTarget();
			$target = $provider->getModel(false)->setId($target);
			//
			$this->_update($source, $target, $link);
			// update replication link
			$link->setData('tstamp', time());
			// catch link
			$this->_transaction->addObject($link);
		}
		// return target
		return $target;
	}
	
	protected function _create(Mage_Core_Model_Abstract $source, Mage_Core_Model_Abstract $target) {
		// get provider
		$provider = $this->_getProvider($source);
		// prefetch source
		$provider->prefetch($source);
		// create replica
		$provider->create($source, $target);
	}
	
	protected function _update(Mage_Core_Model_Abstract $source, Mage_Core_Model_Abstract $target, Typogento_Core_Model_Typo3_Replication_Link $link, $force = false) {
		// prefetch target 
		$provider = $this->_getProvider($target);
		$provider->prefetch($target);
		$targetTimestamp = $provider->getTimestamp($target);
		// prefetch source
		$provider = $this->_getProvider($source);
		$provider->prefetch($source);
		$sourceTimestamp = $provider->getTimestamp($source);
		// validate source and target
		if (!$target->getId() || !$source->getId()) {
			// invalid links
			// @todo prefetch and source :/
			return;
		}
		// return if nothing to do
		if (intval($link->getData('disable')) > 0
			|| $sourceTimestamp < $targetTimestamp
			|| (!$force && $link->getData('tstamp') > $sourceTimestamp)) {
			return;
		}
		// start update transmission
		$provider->update($source, $target);
	}
	
	protected function _getProvider(Mage_Core_Model_Abstract $object) {
		$class = get_class($object);
		return $this->_providers[$class];
	}
	
	protected function _hasProvider(Mage_Core_Model_Abstract $object) {
		$class = get_class($object);
		return isset($this->_providers[$class]);
	}
	
	protected function _getLink(Mage_Core_Model_Abstract $object) {
		// get replication links
		$id = $this->_getProvider($object)->getId();
		$collection = Mage::getModel('typogento/typo3_replication_link')->getCollection()
			->addFieldToFilter('provider', $id);
		
		$collection->getSelect()
			->where('source = ?', $object->getId())
			->where('provider = ?', $id)
			->limit(1);
		
		return $collection->load()->getFirstItem();
	}
	
	protected function _createLink(Mage_Core_Model_Abstract $source, Mage_Core_Model_Abstract $target) {
		// get replication links
		$id = $this->_getProvider($source)->getId();
		
		$link = Mage::getModel('typogento/typo3_replication_link');
		
		$timestamp = time();
		
		$data = array(
			'source' => $source->getId(),
			'target' => $target->getId(),
			'provider' => $id,
			'crdate' => $timestamp,
			'tstamp' => $timestamp
		);
		
		$link->setData($data);
		
		return $link;
	}
	
	protected function _isProcessed(Mage_Core_Model_Abstract $object) {
		if ($object->getId()) {
			// set object as processed
			$key = $this->_getKey($object);
			return isset($this->_processed[$key]);
		}
	}
	protected function _addProcessed(Mage_Core_Model_Abstract $object) {
		if ($object->getId()) {
			// set object as processed
			$key = $this->_getKey($object);
			$this->_processed[$key] = true;
		}
	}
	
	protected function _getKey(Mage_Core_Model_Abstract $object) {
		return get_class($object).$object->getId();
	}
}
