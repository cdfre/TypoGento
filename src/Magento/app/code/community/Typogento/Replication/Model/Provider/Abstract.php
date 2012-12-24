<?php 

abstract class Typogento_Replication_Model_Provider_Abstract {
	
	private $_id = null;
	
	protected $_inProgress = false;
	
	public abstract function prefetch(Mage_Core_Model_Abstract $object);
	
	public abstract function getTimestamp(Mage_Core_Model_Abstract $object);
	public abstract function getDisplay(Mage_Core_Model_Abstract $object);
	public abstract function getModel($source = true);
	public abstract function getCollection();
	public function getId() {
		if ($this->_id === null) {
			$this->_id = md5(get_class($this->getModel()));
		}
		return $this->_id;
	}
	
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
