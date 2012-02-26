<?php

/**
 * TypoGento autoloader
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_weetypogento_autoloader implements t3lib_Singleton {
	
	/**
	 * @var tx_weetypogento_magentoHelper
	 */
	protected static $_helper = null;
	
	/**
	 * Constructor for tx_weetypogento_interface
	 *
	 */
	public function __construct() {
		// init confguration helper if not already done
		if (!isset(self::$_helper)) {
			self::$_helper = t3lib_div::makeInstance('tx_weetypogento_magentoHelper');
		}
		// get the local document root of magento
		$documentRoot = self::$_helper->getDocumentRoot();
		//
		self::_initializeFramework($documentRoot);
	}
	
	/**
	 * Initialize Magento framework
	 * 
	 * Make sure the document root is a valid local path 
	 * without leading directory seperator.
	 * 
	 * @param string $documentRoot Absolute local path to the Magento document root
	 */
	protected static function _initializeFramework($documentRoot) {
		// init magento if it's not already done
		if (class_exists('Mage', false)) {
			return;
		}
		// error reporting
		error_reporting(E_ALL ^ E_NOTICE);
		// compilation includes configuration file
		$file = $documentRoot.'/includes/config.php';
		if (file_exists($file)) {
			include $file;
		}
		// load application
		$file = $documentRoot.'/app/Mage.php';
		if (!file_exists($file)) {
			restore_error_handler();
			tx_weetypogento_div::throwException('lib_document_root_not_valid_error', 
				array($documentRoot)
			);
		}
		require_once $file;
		// restore error reporting
		restore_error_handler();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_autoloader.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_autoloader.php']);
}

?>