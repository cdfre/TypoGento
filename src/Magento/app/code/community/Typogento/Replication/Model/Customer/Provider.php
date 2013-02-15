<?php 

class Typogento_Replication_Model_Customer_Provider extends Typogento_Replication_Model_Provider_Abstract {
	
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
		
		$object->reset();
		$object->cleanAllAddresses();
		$object->load($id);
	}
	
	public function getTimestamp(Mage_Core_Model_Abstract $object) {
		$this->_assertSourceType($object);
		
		$timestamps = array();
		$address = $object->getPrimaryBillingAddress();
		if ($address instanceof Mage_Customer_Model_Address
			&& $address->getData('updated_at')) {
			$timestamps[] = strtotime($address->getData('updated_at'));
		}
		$timestamps[] = strtotime($object->getData('updated_at'));
		// update frontend user
		return max($timestamps);
		
	}
	
	public function getDisplay(Mage_Core_Model_Abstract $object) {
		$this->_assertSourceType($object);
		
		$type  =Mage::helper('typogento_replication')->__('Magento Customer');
		return $object->toString("{{email}} - {$type}");
	}
	
	public function getModel($source = true) {
		if ($source) {
			return Mage::getModel('customer/customer');
		} else {
			return Mage::getModel('typogento_replication/typo3_frontend_user');
		}
	}
	
	public function getCollection() {
		if ($this->_sources === null) {
			$this->_sources = Mage::getModel('customer/customer')->getCollection()
				->addAttributeToSelect('email')
				->addOrder('email', 'ASC');
		}
		return $this->_sources;
	}
	
	protected function _discover(Mage_Core_Model_Abstract $object) {
		$this->_assertSourceType($object);
		
		// set result
		$target = null;
		
		// create frontend user model
		$target = Mage::getModel('typogento_replication/typo3_frontend_user');
		// validate source repository
		if ($target->findEmailDuplicates()) {
			throw new Exception(Mage::helper('typogento_replication')->__('Duplicate email addresses exist in TYPO3 fe_users'));
		}
		if ($target->findCustomerDuplicates()) {
			throw new Exception(Mage::helper('typogento_replication')->__('Duplicate customer links exist in TYPO3 fe_users'));
		}
		
		$read = Mage::getSingleton('core/resource')->getConnection('typogento_read');
		$table = $read->getTableName('fe_users');
		
		if ($object->getId()) {
			$select = $read->select()
				->from(array('t' => $table), array('id' => 'uid'))
				->where('t.tx_typogento_customer = ?', $object->getId())
				->where('t.deleted = 0')
				->where('t.pid = ? ', Mage::getStoreConfig('typogento/typo3_fe/users_pid'))
				->limit(1);
			
			$record = $read->fetchRow($select);
			
			if ($record && isset($record['id'])) {
				$target->setId($record['id']);
			}
		}
		
		if (!$target->getId() && $object->getData('email')) {
			$select = $read->select()
				->from(array('t' => $table), array('id' => 'uid'))
				->where('t.email = ?', $object->getData('email'))
				->where('t.deleted = 0')
				->where('t.pid = ? ', Mage::getStoreConfig('typogento/typo3_fe/users_pid'))
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
		$this->_updateFrontendUser($source, $target);
		
	}
	
	protected function _update(Mage_Core_Model_Abstract $source, Mage_Core_Model_Abstract $target) {
		$this->_assertSourceType($source);
		$this->_assertTargetType($target);
		
		// update frontend user
		$this->_updateFrontendUser($source, $target);
		
	}
	
	protected function _assertSourceType(Mage_Core_Model_Abstract $object) {
		if (!($object instanceof Mage_Customer_Model_Customer)) {
			throw new InvalidArgumentException();
		}
		
	}
	
	protected function _assertTargetType(Mage_Core_Model_Abstract $object) {
		if (!($object instanceof Typogento_Replication_Model_Typo3_Frontend_User)) {
			throw InvalidArgumentException();
		}
	}
	
	protected function _updateFrontendUser(Mage_Customer_Model_Customer $source, Typogento_Replication_Model_Typo3_Frontend_User $target) {
		// validate customer
		$fields = array('firstname', 'lastname', 'email', 'website_id');
		foreach ($fields as $field) {
			if (!$source->getData($field)) {
				throw new Exception(Mage::helper('typogento_replication')->__('Missing first name, last name, email or website'));
			}
		}
		// get data
		$data = array (
			'last_name'          => $source->getData('lastname'),
			'first_name'         => $source->getData('firstname'),
			'name'               => "{$source->getData('firstname')} {$source->getData('lastname')}",
			'email'              => $source->getData('email'),
			'date_of_birth'      => strtotime($source->getData('dob')),
			'tx_typogento_customer' => $source->getId(),
		);
		$gender = $source->getData('gender');
		$gender = intval($gender);
		if ($gender > 0 && $gender < 3) {
			$data['gender'] = strval(--$gender);
		} else {
			$data['gender'] = '99';
		}
		// initialize default values
		if (!$target->getId()) {
			$data['usergroup'] = Mage::getStoreConfig('typogento/typo3_fe/group_uid');
			$random = $source->getRandomPassword();
			$data['password'] = md5($random);
			$data['username'] = $data['email'];
			$data['pid'] = Mage::getStoreConfig('typogento/typo3_fe/users_pid');
		}
		// set data
		$target->addData($data);
		// handling billing address
		$address = $source->getPrimaryBillingAddress();
		if ($address instanceof Mage_Customer_Model_Address) {
			$data = array (
				'address'             => $address->getData('street'),
				'city'                => $address->getData('city'),
				'zip'                 => $address->getData('postcode'),
				'static_info_country' => Mage::getModel('directory/country')->load($address->getData('country_id'))->getIso3Code(),
				'telephone'           => $address->getData('telephone'),
			);
		} else {
			$data = array (
				'address'             => '',
				'city'                => '',
				'zip'                 => '',
				'static_info_country' => '',
				'telephone'           => '',
			);
		}
		// set data
		$target->addData($data)->save();
			
	}
}
