<?php 

class Wee_Typogento_Model_Replication_Provider_Accounts extends Wee_Typogento_Model_Replication_Provider_Abstract {
	
	const PROVIDER_ID_TYPO3_FRONTEND_USER = 1;
	const PROVIDER_ID_MAGENTO_CUSTOMER = 2;
	
	protected $_id = null;
	
	protected $_sources = null;
	
	public function __construct($id) {
		if ($id !== self::PROVIDER_ID_TYPO3_FRONTEND_USER && $id !== self::PROVIDER_ID_MAGENTO_CUSTOMER) {
			throw new InvalidArgumentException(Mage::helper('typogento')->__('Unexpected provider id'));
		}
		
		$this->_id = $id;
	}
	/**
	 * @todo Preserve
	 * @see Wee_Typogento_Model_Replication_Provider_Abstract::prefetch()
	 */
	public function prefetch(Mage_Core_Model_Abstract $object) {
		$this->_assertSourceType($object);
		
		$id = $object->getId();
		
		if (!$id) {
			return;
		}
		
		if ($this->_id === self::PROVIDER_ID_MAGENTO_CUSTOMER) {
			$object->reset();
			$object->cleanAllAddresses();
			$object->load($id);
		} else {
			$object->unsetData();
			$object->load($id);
		}
	}
	
	public function getTimestamp(Mage_Core_Model_Abstract $object) {
		$this->_assertSourceType($object);
		
		if ($this->_id === self::PROVIDER_ID_MAGENTO_CUSTOMER) {
			$timestamps = array();
			$address = $object->getPrimaryBillingAddress();
			if ($address instanceof Mage_Customer_Model_Address
				&& $address->getData('updated_at')) {
				$timestamps[] = strtotime($address->getData('updated_at'));
			}
			$timestamps[] = strtotime($object->getData('updated_at'));
			// update frontend user
			return max($timestamps);
		} else {
			// update frontend user
			return $object->getData('tstamp');
		}
	}
	
	public function getDisplay(Mage_Core_Model_Abstract $object) {
		$this->_assertSourceType($object);
		
		if ($this->_id === self::PROVIDER_ID_MAGENTO_CUSTOMER) {
			$type  =Mage::helper('typogento')->__('Magento Customer');
			return $object->toString("{{email}} - {$type}");
		} else {
			$type  =Mage::helper('typogento')->__('TYPO3 Frontend User');
			return $object->toString("{{email}} - {$type}");
		}
	}
	
	public function getModel($source = true) {
		if (($this->_id === self::PROVIDER_ID_TYPO3_FRONTEND_USER && !$source
			|| $this->_id === self::PROVIDER_ID_MAGENTO_CUSTOMER && $source)) {
			return Mage::getModel('customer/customer');
		} else {
			return Mage::getModel('typogento/typo3_frontend_user');
		}
	}
	
	public function getCollection() {
		if ($this->_id === self::PROVIDER_ID_MAGENTO_CUSTOMER) {
			if ($this->_sources === null) {
				$this->_sources = Mage::getModel('customer/customer')->getCollection()
					->addAttributeToSelect('email')
					->addOrder('email', 'ASC');
			}
			return $this->_sources;
		} else {
			if ($this->_sources === null) {
				$this->_sources = Mage::getModel('typogento/typo3_frontend_user')->getCollection()
					->addOrder('email', 'ASC');
				$this->_sources->getSelect()
					->columns(array(
						'email' => 'email',
					));
			}
			return $this->_sources;
		}
	}
	
	public function getId() {
		return $this->_id;
	}
	
	protected function _discover(Mage_Core_Model_Abstract $object) {
		$this->_assertSourceType($object);
		
		// set result
		$target = null;
		// check source type
		if ($this->_id === self::PROVIDER_ID_MAGENTO_CUSTOMER) {
			// create frontend user model
			$target = Mage::getModel('typogento/typo3_frontend_user');
			// validate source repository
			if ($target->findEmailDuplicates()) {
				throw new Exception(Mage::helper('typogento')->__('Duplicate email addresses exist in TYPO3 fe_users'));
			}
			if ($target->findCustomerDuplicates()) {
				throw new Exception(Mage::helper('typogento')->__('Duplicate customer links exist in TYPO3 fe_users'));
			}
			
			$read = Mage::getSingleton('core/resource')->getConnection('typogento_read');
			$table = $read->getTableName('fe_users');
			
			if ($object->getId()) {
				$select = $read->select()
					->from(array('t' => $table), array('id' => 'uid'))
					->where('t.tx_weetypogento_customer = ?', $object->getId())
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
		} else {
			// create customer model
			$target = Mage::getModel('customer/customer')->setWebsiteId($website);
			
			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$table = $read->getTableName('customer_entity');
			
			if ($object->getData('tx_weetypogento_customer')) {
				$select = $read->select()
					->from(array('t' => $table), array('id' => 'entity_id'))
					->where('t.entity_id = ?', $object->getData('tx_weetypogento_customer'))
					->where('t.website_id = ?', Mage::helper('typogento/typo3')->getWebsiteId())
					->limit(1);
				
				$record = $read->fetchRow($select);
				
				if ($record && isset($record['id'])) {
					$target->setId($record['id']);
				}
			} else if ($object->getData('email')) {
				$select = $read->select()
					->from(array('t' => $table), array('id' => 'entity_id'))
					->where('t.email = ?', $source->getData('email'))
					->where('t.website_id = ?', Mage::helper('typogento/typo3')->getWebsiteId())
					->limit(1);
				
				$record = $read->fetchRow($select);
				
				if ($record && isset($record['id'])) {
					$target->setId($record['id']);
				}
			}
			
		}
		
		return $target;
	}
	
	protected function _create(Mage_Core_Model_Abstract $source, Mage_Core_Model_Abstract $target) {
		$this->_assertSourceType($source);
		$this->_assertTargetType($target);
		
		if ($this->_id === self::PROVIDER_ID_MAGENTO_CUSTOMER) {
			// update frontend user
			$this->_updateFrontendUser($source, $target);
		} else {
			// update frontend user
			$this->_updateCustomer($source, $target);
		}
	}
	
	protected function _update(Mage_Core_Model_Abstract $source, Mage_Core_Model_Abstract $target) {
		$this->_assertSourceType($source);
		$this->_assertTargetType($target);
		
		if ($source instanceof Mage_Customer_Model_Customer) {
			// update frontend user
			$this->_updateFrontendUser($source, $target);
		} else if($source instanceof Wee_Typogento_Model_Typo3_Frontend_User) {
			// update frontend user
			$this->_updateCustomer($source, $target);
		}
	}
	
	protected function _assertSourceType(Mage_Core_Model_Abstract $object) {
		if ($this->_id === self::PROVIDER_ID_MAGENTO_CUSTOMER) {
			if (!($object instanceof Mage_Customer_Model_Customer)) {
				throw new InvalidArgumentException();
			}
		} else {
			if (!($object instanceof Wee_Typogento_Model_Typo3_Frontend_User)) {
				throw new InvalidArgumentException();
			}
		}
	}
	
	protected function _assertTargetType(Mage_Core_Model_Abstract $object) {
		if ($this->_id === self::PROVIDER_ID_MAGENTO_CUSTOMER) {
			if (!($object instanceof Wee_Typogento_Model_Typo3_Frontend_User)) {
				throw InvalidArgumentException();
			}
		} else {
			if (!($object instanceof Mage_Customer_Model_Customer)) {
				throw InvalidArgumentException();
			}
		}
	}
	
	protected function _updateFrontendUser(Mage_Customer_Model_Customer $source, Wee_Typogento_Model_Typo3_Frontend_User $target) {
		// validate customer
		$fields = array('firstname', 'lastname', 'email', 'website_id');
		foreach ($fields as $field) {
			if (!$source->getData($field)) {
				throw new Exception(Mage::helper('typogento')->__('Missing first name, last name, email or website'));
			}
		}
		// get data
		$data = array (
			'last_name'          => $source->getData('lastname'),
			'first_name'         => $source->getData('firstname'),
			'name'               => "{$source->getData('firstname')} {$source->getData('lastname')}",
			'email'              => $source->getData('email'),
			'date_of_birth'      => strtotime($source->getData('dob')),
			'tx_weetypogento_customer' => $source->getId(),
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
	
	protected function _updateCustomer(Wee_Typogento_Model_Typo3_Frontend_User $source, Mage_Customer_Model_Customer $target) {
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
			$data['website_id'] = Mage::helper('typogento/typo3')->getWebsiteId();
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
