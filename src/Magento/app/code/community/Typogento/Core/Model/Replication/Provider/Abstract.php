<?php 

abstract class Typogento_Core_Model_Replication_Provider_Abstract {
	
	protected $_inProgress = false;
	
	public abstract function prefetch(Mage_Core_Model_Abstract $object);
	
	public abstract function getTimestamp(Mage_Core_Model_Abstract $object);
	public abstract function getDisplay(Mage_Core_Model_Abstract $object);
	public abstract function getModel($source = true);
	public abstract function getCollection();
	public abstract function getId();
	
	public function discover(Mage_Core_Model_Abstract $object) {
		if ($this->_inProgress) {
			throw new RuntimeException();
		}
	
		$this->_inProgress = true;
		try {
			$result = $this->_discover($object);
			$this->_inProgress = false;
			return $result;
		} catch (Exception $e) {
			$this->_inProgress = false;
			throw $e;
		}
		
	}
	
	public function create(Mage_Core_Model_Abstract $source, Mage_Core_Model_Abstract $target) {
		if ($this->_inProgress) {
			throw new RuntimeException();
		}
		
		$this->_inProgress = true;
		try {
			$this->_create($source, $target);
		} catch (Exception $e) {
			$this->_inProgress = false;
			throw $e;
		}
		$this->_inProgress = false;
	}
	
	public function update(Mage_Core_Model_Abstract $source, Mage_Core_Model_Abstract $target) {
		if ($this->_inProgress) {
			throw new RuntimeException();
		}
		
		$this->_inProgress = true;
		try {
			$this->_update($source, $target);
		} catch (Exception $e) {
			$this->_inProgress = false;
			throw $e;
		}
		$this->_inProgress = false;
	}
	
	protected abstract function _create(Mage_Core_Model_Abstract $source, Mage_Core_Model_Abstract $target);
	protected abstract function _update(Mage_Core_Model_Abstract $source, Mage_Core_Model_Abstract $target);
	protected abstract function _discover(Mage_Core_Model_Abstract $object);
}
