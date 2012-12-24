<?php 

/**
 * 
 *
 */
class tx_typogento_statusReport implements tx_reports_StatusProvider {
	
	/**
	 * Constructor for class tx_typogento_statusReport
	 */
	public function __construct() {
		$GLOBALS['LANG']->includeLLFile('EXT:typogento/reports/locallang.xml');
	}

	/**
	 * Create status report
	 *
	 * @see typo3/sysext/reports/interfaces/tx_reports_StatusProvider::getStatus()
	 * @return array
	 */
	public function getStatus() {
		// set the report array
		$reports = array(
			$this->_getApiStatus(),
			$this->_getSystemStatus(),
			$this->_getConnectorStatus(),
			$this->_getReplicationStatus(),
		);
		// return the report
		return $reports;
	}
	
	protected function _getSystemStatus() {
		$value    = $GLOBALS['LANG']->getLL('status_available');
		$message  = '';
		$severity = tx_reports_reports_status_Status::OK;
		// check magento system
		try {
			// init the autoloader
			t3lib_div::makeInstance('tx_typogento_autoloader');
			// init the application
			Mage::app();
		} catch (Exception $e) {
			$message  = $this->_renderFlashMessage($e->getMessage(), t3lib_FlashMessage::WARNING);
		}
		//
		if (!empty($message)) {
			$message = "<p>{$GLOBALS['LANG']->getLL('status_system_has_issues')}</p>{$message}";
			$severity = tx_reports_reports_status_Status::ERROR;
			$value    = $GLOBALS['LANG']->getLL('status_not_available');
		}
		// return status
		return t3lib_div::makeInstance('tx_reports_reports_status_Status',
			$GLOBALS['LANG']->getLL('status_system_title'), $value, $message, $severity
		);
	}
	
	protected function _getConnectorStatus() {
		$value    = $GLOBALS['LANG']->getLL('status_available');
		$message  = '';
		$severity = tx_reports_reports_status_Status::OK;
		// check magento connector
		try {
			// validate system status
			if ($this->_getSystemStatus()->getSeverity() === tx_reports_reports_status_Status::ERROR) {
				throw new Exception($GLOBALS['LANG']->getLL('status_system_not_available'));
			}
			// validate connector is installed and enabled
			if (Mage::helper('core')->isModuleEnabled('Typogento_Core') === false) {
				throw new Exception($GLOBALS['LANG']->getLL('status_connector_not_installed_or_enabled'));
			}
			// get helpers
			$helper = Mage::helper('typogento_core/typo3');
			$select = t3lib_div::makeInstance('t3lib_pageSelect');
			// validate database connection
			if ($helper->validateDatabaseConnection() === false) {
				$message .= $this->_renderFlashMessage(
					$GLOBALS['LANG']->getLL('status_connector_invalid_database_connection'), 
					t3lib_FlashMessage::WARNING);
			}
			// validate frontend users system folder
			if (!$select->checkRecord('pages', $helper->getFrontendUsersPageId(), true)) {
				$message .= $this->_renderFlashMessage(
					$GLOBALS['LANG']->getLL('status_connector_invalid_frontend_users_system_folder'),
					t3lib_FlashMessage::WARNING);
			}
			// validate frontend users group
			if (!$select->checkRecord('fe_groups', $helper->getFrontendUsersGroupId())) {
				$message .= $this->_renderFlashMessage(
					$GLOBALS['LANG']->getLL('status_connector_invalid_frontend_users_group'),
					t3lib_FlashMessage::WARNING);
			}
		} catch (Exception $e) {
			$message = $this->_renderFlashMessage($e->getMessage(), t3lib_FlashMessage::WARNING);
		}
		//
		if (!empty($message)) {
			$message = "<p>{$GLOBALS['LANG']->getLL('status_connector_has_issues')}</p>{$message}";
			$severity = tx_reports_reports_status_Status::ERROR;
			$value    = $GLOBALS['LANG']->getLL('status_not_available');
		}
		// return status
		return t3lib_div::makeInstance('tx_reports_reports_status_Status',
			$GLOBALS['LANG']->getLL('status_connector_title'), $value, $message, $severity
		);
	}
	
	protected function _getReplicationStatus() {
		$message  = '';
		$severity = tx_reports_reports_status_Status::OK;
		$value    = $GLOBALS['LANG']->getLL('status_available');
		// check magento replication
		try {
			// validate connector status
			if ($this->_getConnectorStatus()->getSeverity() === tx_reports_reports_status_Status::ERROR) {
				throw new Exception($GLOBALS['LANG']->getLL('status_connector_not_available'));
			}
			// get frontend user model
			$user = Mage::getModel('typogento_replication/typo3_frontend_user');
			// validate frontend users data
			if ($user->findEmailDuplicates()) {
				$message .= $this->_renderFlashMessage(
					$GLOBALS['LANG']->getLL('status_frontend_users_email_duplicates_found'),
					t3lib_FlashMessage::WARNING);
			}
			if ($user->findCustomerDuplicates()) {
				$message .= $this->_renderFlashMessage(
					$GLOBALS['LANG']->getLL('status_frontend_users_customer_link_duplicates_found'),
					t3lib_FlashMessage::WARNING);
			}
		} catch (Exception $e) {
			$message = $this->_renderFlashMessage($e->getMessage(), t3lib_FlashMessage::WARNING);
		}
		//
		if (!empty($message)) {
			$message = "<p>{$GLOBALS['LANG']->getLL('status_replication_has_issues')}</p>{$message}";
			$severity = tx_reports_reports_status_Status::ERROR;
			$value    = $GLOBALS['LANG']->getLL('status_not_available');
		}
		// return status
		return t3lib_div::makeInstance('tx_reports_reports_status_Status',
			$GLOBALS['LANG']->getLL('status_replication_title'), $value, $message, $severity
		);
	}
	
	protected function _getApiStatus() {
		$message  = '';
		$severity = tx_reports_reports_status_Status::OK;
		$value    = $GLOBALS['LANG']->getLL('status_available');
		// check magento api
		try {
			// check api is available
			t3lib_div::makeInstance('tx_typogento_soapinterface')->isAvailable();
		} catch (Exception $e) {
			$message = $this->_renderFlashMessage($e->getMessage(), t3lib_FlashMessage::WARNING);
		}
		//
		if (!empty($message)) {
			$message = "<p>{$GLOBALS['LANG']->getLL('status_api_has_issues')}</p>{$message}";
			$severity = tx_reports_reports_status_Status::ERROR;
			$value    = $GLOBALS['LANG']->getLL('status_not_available');
		}
		// return status
		return t3lib_div::makeInstance('tx_reports_reports_status_Status',
			$GLOBALS['LANG']->getLL('status_api_title'), $value, $message, $severity
		);
	}
	
	protected function _renderFlashMessage($message, $severity) {
		return t3lib_div::makeInstance('t3lib_FlashMessage',
			$message, '', $severity)->render();
	}
}
?>
