<?php 

class Typogento_Replication_Model_Typo3_Frontend_User_Provider extends Typogento_Replication_Model_Provider_Abstract {
	
	protected $_sources = null;
	
	public function __construct() {
		
	}
	
	/**
	 * @todo Preserve
	 * @see Typogento_Core_Model_Replication_Provider_Abstract::prefetch()
	 */
	public function prefetch(Mage_Core_Model_Abstract $object) {
		$this->_assertSourceType($object);
		
		$id = $object->getId();
		
		if (!$id) {
			return;
		}
		
		$object->unsetData();
		$object->load($id);
	}
	
	public function getTimestamp(Mage_Core_Model_Abstract $object) {
		$this->_assertSourceType($object);
		
		// update frontend user
		return $object->getData('tstamp');
	}
	
	public function getDisplay(Mage_Core_Model_Abstract $object) {
		$this->_assertSourceType($object);
		
		$type = Mage::helper('typogento_replication')->__('TYPO3 Frontend User');
		return $object->toString("{{email}} - {$type}");
	}
	
	public function getModel($source = true) {
		if (!$source) {
			return Mage::getModel('customer/customer');
		} else {
			return Mage::getModel('typogento_replication/typo3_frontend_user');
		}
	}
	
	public function getCollection() {
		if ($this->_sources === null) {
			$this->_sources = Mage::getModel('typogento_replication/typo3_frontend_user')->getCollection()
				->addOrder('email', 'ASC');
			$this->_sources->getSelect()
				->columns(array(
					'email' => 'email',
				));
		}
		return $this->_sources;
	}
	
	protected function _discover(Mage_Core_Model_Abstract $object) {
		$this->_assertSourceType($object);
		
		// set result
		$target = null;
		
		// create customer model
		$target = Mage::getModel('customer/customer')->setWebsiteId($website);
		
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$table = $read->getTableName('customer_entity');
		
		if ($object->getData('tx_typogento_customer')) {
			$select = $read->select()
				->from(array('t' => $table), array('id' => 'entity_id'))
				->where('t.entity_id = ?', $object->getData('tx_typogento_customer'))
				->where('t.website_id = ?', Mage::helper('typogento_core/typo3')->getWebsiteId())
				->limit(1);
			
			$record = $read->fetchRow($select);
			
			if ($record && isset($record['id'])) {
				$target->setId($record['id']);
			}
		} else if ($object->getData('email')) {
			$select = $read->select()
				->from(array('t' => $table), array('id' => 'entity_id'))
				->where('t.email = ?', $object->getData('email'))
				->where('t.website_id = ?', Mage::helper('typogento_core/typo3')->getWebsiteId())
				->limit(1);
			
			$record = $read->fetchRow($select);
			
			if ($record && isset($record['id'])) {
				$target->setId($record['id']);
			}
		}
		
		return $target;
	}
	
	protected function _create(Mage_Core_Model_Abstract $source, Mage_Core_Model_Abstract $target) {
		$this->_assertSourceType($source);
		$this->_assertTargetType($target);
		
		// update frontend user
		$this->_updateCustomer($source, $target);
	}
	
	protected function _update(Mage_Core_Model_Abstract $source, Mage_Core_Model_Abstract $target) {
		$this->_assertSourceType($source);
		$this->_assertTargetType($target);
		
		// update frontend user
		$this->_updateCustomer($source, $target);
	}
	
	protected function _assertSourceType(Mage_Core_Model_Abstract $object) {
		if (!($object instanceof Typogento_Replication_Model_Typo3_Frontend_User)) {
			throw new InvalidArgumentException();
		}
	}
	
	protected function _assertTargetType(Mage_Core_Model_Abstract $object) {
		if (!($object instanceof Mage_Customer_Model_Customer)) {
			throw InvalidArgumentException();
		}
	}
	
	protected function _updateCustomer(Typogento_Replication_Model_Typo3_Frontend_User $source, Mage_Customer_Model_Customer $target) {
		//
		$transaction = Mage::getModel('core/resource_transaction');
		// get data
		$data = array(
			'email'     => $source->getData('email'),
			'lastname'  => $source->getData('last_name'),
			'firstname' => $source->getData('first_name'),
			'is_active' => !$source->getData('disabled'),
			'dob'       => date('Y-m-d', $source->getData('date_of_birth'))
		);
		$gender = $source->getData('gender');
		$gender = intval($gender);
		if ($gender > -1 && $gender < 2) {
			$data['gender'] = ++$gender;
		}
		// initialize default values
		if (!$target->getId()) {
			$data['group_id'] = 1;
			$random = $target->getRandomPassword();
			$data['password_hash'] = Mage::helper('core')->getHash($random, true);
			$data['created_at'] = date('Y-m-d', $source->getData('crdate'));
			$data['website_id'] = Mage::helper('typogento_core/typo3')->getWebsiteId();
		}
		// set data
		$target->addData($data);
		$transaction->addObject($target);
		// handling billing address
		$address = $target->getPrimaryBillingAddress();
		if ($source->getData('static_info_country')
			&& $source->getData('address')
			&& $source->getData('city')
			&& $source->getData('telephone')) {
			// add or update default billing address
			if (!$address instanceof Mage_Customer_Model_Address) {
				$address = Mage::getModel('customer/address');
			}
			$country = Mage::getModel('directory/country');
			$country->loadByCode($source->getData('static_info_country'));
			$street = preg_split('/\n|\r|\r\n/', $source->getData('address'), 2, PREG_SPLIT_NO_EMPTY);
			if (isset($street[1])) {
				$street[1] = preg_replace('/\n|\r|\r\n/', '', $street[1]);
			}
			$data = array (
				'firstname' =>  $source->getData('first_name'),
				'lastname'  => $source->getData('last_name'),
				'street'    => $street,
				'city' => $source->getData('city'),
				'postcode' => $source->getData('zip'),
				'country_id' => $country->getId(),
				'telephone' => $source->getData('telephone'),
			);
			$address->addData($data);
			if (!$address->getId()) {
				$address->setIsDefaultBilling(true);
				if ($target->getDefaultBilling()) {
					$target->setData('default_billing', '');
				}
				$target->addAddress($address);
			}
			$transaction->addObject($address);
		} else if (!$source->getData('static_info_country')
			&& !$source->getData('address')
			&& !$source->getData('city')
			&& !$source->getData('telephone')) {
			// remove default billing address
			if ($address instanceof Mage_Customer_Model_Address) {
				$target->setData('default_billing', '');
				$address->delete();
				$transaction->addObject($address);
			}
		}
		$transaction->save();
	}
}
