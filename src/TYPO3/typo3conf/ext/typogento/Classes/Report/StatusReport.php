<?php 

namespace Tx\Typogento\Report;

use Tx\Typogento\Utility\LocalizationUtility;
use Tx\Typogento\Core\Bootstrap;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Reports\Status;

use Mage;

use Exception;

/**
 * TypoGento status report.
 *
 */
class StatusReport implements \TYPO3\CMS\Reports\StatusProviderInterface {

	/**
	 * Create status report
	 *
	 * @see \TYPO3\CMS\Reports\StatusProviderInterface::getStatus()
	 * @return array
	 */
	public function getStatus() {
		// set the report array
		$reports = array(
			$this->getApiStatus(),
			$this->getSystemStatus(),
			$this->getConnectorStatus(),
			$this->getReplicationStatus(),
		);
		// return the report
		return $reports;
	}
	
	/**
	 * Check the Magento system status.
	 * 
	 * @return \TYPO3\CMS\Reports\Status
	 */
	protected function getSystemStatus() {
		$value    = LocalizationUtility::translate('report.status.available');
		$message  = '';
		$severity = Status::OK;
		// check magento system
		try {
			// init the autoloader
			Bootstrap::initialize();
			// init the application
			Mage::app();
		} catch (Exception $e) {
			$message = $this->renderFlashMessage($e->getMessage(), FlashMessage::WARNING);
		}
		//
		if (!empty($message)) {
			$prologue = LocalizationUtility::translate('report.status.system.has_issues');
			$message  = "<p>{$prologue}</p>{$message}";
			$severity = Status::ERROR;
			$value    = LocalizationUtility::translate('report.status.not_available');
		}
		// return status
		return GeneralUtility::makeInstance('\\TYPO3\\CMS\\Reports\\Status',
			LocalizationUtility::translate('report.status.system.title'), $value, $message, $severity
		);
	}
	
	/**
	 * Check the Magento connector status.
	 * 
	 * @throws Exception
	 * @return \TYPO3\CMS\Reports\Status
	 */
	protected function getConnectorStatus() {
		$value    = LocalizationUtility::translate('report.status.available');
		$message  = '';
		$severity = Status::OK;
		// check magento connector
		try {
			// validate system status
			if ($this->getSystemStatus()->getSeverity() === Status::ERROR) {
				throw new \Exception(LocalizationUtility::translate('report.status.system.not_available'));
			}
			// validate connector is installed and enabled
			if (Mage::helper('core')->isModuleEnabled('Typogento_Core') === false) {
				throw new \Exception(LocalizationUtility::translate('report.status.connector.not_installed_or_enabled'));
			}
			// get helpers
			$helper = Mage::helper('typogento_core/typo3');
			$select = GeneralUtility::makeInstance('t3lib_pageSelect');
			// validate database connection
			if ($helper->validateDatabaseConnection() === false) {
				$message .= $this->renderFlashMessage(
					LocalizationUtility::translate('report.status.connector.invalid_database_connection'), 
					FlashMessage::WARNING);
			}
			// get helper
			$helper = Mage::helper('typogento_replication/typo3_frontend_user');
			// validate frontend users system folder
			if (!$select->checkRecord('pages', $helper->getPageId(), true)) {
				$message .= $this->renderFlashMessage(
					LocalizationUtility::translate('report.status.connector.invalid_frontend_users_folder'),
					FlashMessage::WARNING);
			}
			// validate frontend users group
			if (!$select->checkRecord('fe_groups', $helper->getGroupId())) {
				$message .= $this->renderFlashMessage(
					LocalizationUtility::translate('report.status.connector.invalid_frontend_users_group'),
					FlashMessage::WARNING);
			}
		} catch (Exception $e) {
			$message = $this->renderFlashMessage($e->getMessage(), FlashMessage::WARNING);
		}
		//
		if (!empty($message)) {
			$prologue = LocalizationUtility::translate('report.status.connector.has_issues');
			$message  = "<p>{$prologue}</p>{$message}";
			$severity = Status::ERROR;
			$value    = LocalizationUtility::translate('report.status.not_available');
		}
		// return status
		return GeneralUtility::makeInstance('\\TYPO3\\CMS\\Reports\\Status',
			LocalizationUtility::translate('report.status.connector.title'), $value, $message, $severity
		);
	}
	
	/**
	 * Check the Magento replication status.
	 * 
	 * @throws Exception
	 * @return \TYPO3\CMS\Reports\Status
	 */
	protected function getReplicationStatus() {
		$message  = '';
		$severity = Status::OK;
		$value    = LocalizationUtility::translate('report.status.available');
		// check magento replication
		try {
			// validate connector status
			if ($this->getConnectorStatus()->getSeverity() === Status::ERROR) {
				throw new \Exception(LocalizationUtility::translate('report.status.connector.not_available'));
			}
			// get frontend user model
			$user = Mage::getModel('typogento_replication/typo3_frontend_user');
			// validate frontend users data
			if ($user->findEmailDuplicates()) {
				$message .= $this->renderFlashMessage(
					LocalizationUtility::translate('report.status.replication.duplicate_frontend_user_emails'),
					FlashMessage::WARNING);
			}
			if ($user->findCustomerDuplicates()) {
				$message .= $this->renderFlashMessage(
					LocalizationUtility::translate('report.status.replication.duplicate_frontend_user_customer_links'),
					FlashMessage::WARNING);
			}
		} catch (Exception $e) {
			$message = $this->renderFlashMessage($e->getMessage(), FlashMessage::WARNING);
		}
		//
		if (!empty($message)) {
			$prologue = LocalizationUtility::translate('report.status.replication.has_issues');
			$message  = "<p>{$prologue}</p>{$message}";
			$severity = Status::ERROR;
			$value    = LocalizationUtility::translate('report.status.not_available');
		}
		// return status
		return GeneralUtility::makeInstance('\\TYPO3\\CMS\\Reports\\Status',
			LocalizationUtility::translate('report.status.replication.title'), $value, $message, $severity
		);
	}
	
	/**
	 * Check the Magento API status.
	 * 
	 * @return \TYPO3\CMS\Reports\Status
	 */
	protected function getApiStatus() {
		$message  = '';
		$severity = Status::OK;
		$value    = LocalizationUtility::translate('report.status.available');
		// check magento api
		try {
			// check api is available
			GeneralUtility::makeInstance('Tx\\Typogento\\Service\\SoapService')->isAvailable();
		} catch (Exception $e) {
			$message = $this->renderFlashMessage($e->getMessage(), FlashMessage::WARNING);
		}
		//
		if (!empty($message)) {
			$prologue = LocalizationUtility::translate('report.status.api.has_issues');
			$message  = "<p>{$prologue}</p>{$message}";
			$severity = Status::ERROR;
			$value    = LocalizationUtility::translate('report.status.not_available');
		}
		// return status
		return GeneralUtility::makeInstance('TYPO3\\CMS\\Reports\\Status',
			LocalizationUtility::translate('report.status.api.title'), $value, $message, $severity
		);
	}
	
	/**
	 * Render a flash message.
	 * 
	 * @param string $message
	 * @param integer $severity
	 */
	protected function renderFlashMessage($message, $severity) {
		return GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage', 
			$message, '', $severity)->render();
	}
}
?>
