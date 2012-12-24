<?php 

/**
 * TypoGento customer overrides
 * 
 * Extends the customer authentication for in-box single sign-on, 
 * password synchronisation and account replication.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Authentication_Model_Customer extends Mage_Customer_Model_Customer {

	public function authenticate($login, $password) {
		// 
		$helper = Mage::helper('typogento_core/typo3');
		// use default provider if typogento not active
		if (!$helper->isFrontendActive()
			|| !$helper->validateDatabaseConnection()) {
			return parent::authenticate($login, $password);
		}
		// preserve data for some cases
		$data = array(
			'email' => $login,
			'website_id' => $this->getData('website_id')
		);
		// set the result
		$success = false;
		// use default provider
		try {
			if (!parent::authenticate($login, $password)) {
				throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Invalid login or password.'),
					self::EXCEPTION_INVALID_EMAIL_OR_PASSWORD
				);
			}
		} catch (Exception $e) {
			// try replication if customer not exist
			if (!$this->getId()) {
				try {
					// restore data
					$this->unsetData()->setData($data);
					// get replication manager
					$manager = Mage::getSingleton('typogento_replication/manager');
					// discover frontend user
					$user = $manager->discover($this);
					// validate frontend user
					if ($user instanceof Typogento_Replicataion_Model_Typo3_Frontend_User && $user->getId()) {
						// replicate frontend user
						$manager->replicate($user, 'accounts');
						// load customer
						$this->loadByEmail($login);
					}
				} catch (Exception $e) {
					Mage::logException($e);
				}
			}
			// throw not if password validation failed only
			if ($e->getCode() !== parent::EXCEPTION_INVALID_EMAIL_OR_PASSWORD || !$this->getId()) {
				throw $e;
			}
			// load frontend user
			$user = Mage::getModel('typogento_replication/typo3_frontend_user');
			$user->load($this->getId(), 'tx_typogento_customer');
			// use frontend user provider
			if (!$user->authenticate($password)) {
				throw $e;
			}
			try {
				// synchronize password
				$this->setPassword($password);
				$this->save();
			} catch (Exception $e) {}
		}
		// dispatch authenticated
		Mage::dispatchEvent('customer_customer_authenticated', array(
			'model'    => $this,
			'password' => $password,
		));
		// return success
		return true;
	}
}
