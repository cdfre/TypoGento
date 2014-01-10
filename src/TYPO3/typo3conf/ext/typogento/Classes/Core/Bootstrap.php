<?php

namespace Tx\Typogento\Core;

use \Tx\Typogento\Utility\ConfigurationUtility;

/**
 * Bootstrapper
 * 
 * Initializes the Magento framework.
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Bootstrap implements \TYPO3\CMS\Core\SingletonInterface {
	
	/**
	 * Initialize the Magento framework
	 * 
	 * @param string $path Absolute local path to the Magento document root. Make sure the document root is a valid local path without leading directory seperator.
	 * @throws Exception If the path is not valid.
	 */
	public static function initialize($path = null) {
		// init magento if it's not already done
		if (class_exists('Mage', false)) {
			return;
		}
		// 
		if ($path === null) {
			// get the local document root of magento
			$path = ConfigurationUtility::getDocumentRoot();
		}
		// error reporting
		error_reporting(E_ALL ^ E_NOTICE);
		// compilation includes configuration file
		$file = $path.'/includes/config.php';
		if (file_exists($file)) {
			include $file;
		}
		// load application
		$file = $path.'/app/Mage.php';
		if (!file_exists($file)) {
			restore_error_handler();
			throw new Exception(sprintf('The document root "%s" is not valid', $path), 1356844194);
		}
		require_once $file;
		// replace autloader
		if (class_exists('Typogento_Core_Model_Autoload')) {
			spl_autoload_register(array(\Typogento_Core_Model_Autoload::instance(), 'autoload'));
			spl_autoload_unregister(array(\Varien_Autoload::instance(), 'autoload'));
		} else {
			throw new Exception('The Magento connector module is not installed.', 1389397885);
		}
		// restore error reporting
		restore_error_handler();
	}
}
?>