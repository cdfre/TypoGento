<?php

/**
 * TypoGento autoloader
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_weetypogento_autoloader implements t3lib_Singleton {
	
	/**
	 * Constructor for tx_weetypogento_interface
	 *
	 */
	public function __construct() {
		// get the local document root of magento
		$path = &tx_weetypogento_tools::getExtConfig('path');
		//
		self::_initializeFramework($path);
	}
	
	/**
	 * Initialize Magento framework
	 * 
	 * @param string $path Absolute local path to the Magento root
	 */
	protected static function _initializeFramework($path) {
		if (empty($path)) {
			throw new Exception('Root directory is not set');
		}
		
		if (!is_dir($path)) {
			throw new Exception(sprintf('Root directory \'%s\' not found', $path));
		}
		// init magento if it's not already done
		if (class_exists('Mage', false)) {
			return;
		}
		// error reporting
		error_reporting(E_ALL ^ E_NOTICE);
		// compilation includes configuration file
		$file = $path.'includes/config.php';
		if (file_exists($file)) {
			include $file;
		}
		// load application
		$file = $path.'app/Mage.php';
		if (!file_exists($file)) {
			restore_error_handler();
			throw new Exception(sprintf('Invalid root directory \'%s\'', $path));
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