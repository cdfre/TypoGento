<?php 

/**
 * TypoGento Magento helper
 *
 *
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

class tx_weetypogento_magentoHelper implements t3lib_Singleton {

	protected static $_helper = null;
	
	protected  static $_data = array();
	
	public function __construct() {
		if (!isset(self::$_helper)) {
			self::$_helper = t3lib_div::makeInstance('tx_weetypogento_configurationHelper');
		}
	}
	
	public function getDocumentRoot() {
		// 
		if (!isset(self::$_data['document_root'])) {
			
			$value = self::$_helper->getValue(tx_weetypogento_configurationHelper::EXTENSION_MANAGER, 'path');
			$path = realpath($value);
			
			if ($path === false) {
				tx_weetypogento_div::throwException('lib_invalid_document_root_error', 
					array($value)
				);
			}
			
			self::$_data['document_root'] = $path;
		}
		
		return self::$_data['document_root'];
	}
	
	public function getBaseUrl() {
		//
		if (!isset(self::$_data['base_url'])) {
			
			$value = self::$_helper->getValue(tx_weetypogento_configurationHelper::EXTENSION_MANAGER, 'url');
			$url = filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
			
			if ($url === false) {
				tx_weetypogento_div::throwException('lib_invalid_base_url_error', 
					array($value)
				);
			}
			
			$components = parse_url($url);
			
			$components['port'] = trim($components['port']) === ''
				? '' : ':'.$components['port'];
			$components['scheme'] = trim($components['scheme']) === ''
				? '' : $components['scheme'].'://';
			
			self::$_data['base_url'] = $components['scheme'].$components['host'].$components['port'].$components['path'];
		}
		
		return self::$_data['base_url'];
	}
	
	public function getApiAccount() {
		return self::$_helper->getValue(tx_weetypogento_configurationHelper::EXTENSION_MANAGER, 'username');
	}
	
	public function getApiPassword() {
		return self::$_helper->getValue(tx_weetypogento_configurationHelper::EXTENSION_MANAGER, 'password');
	}
	
	public function getWebsiteId() {
		return self::$_helper->getValue(tx_weetypogento_configurationHelper::EXTENSION_MANAGER, 'website');
	}
}
?>
