<?php 

/**
 * TypoGento Magento helper
 *
 *
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

class tx_typogento_magentoHelper implements t3lib_Singleton {

	protected static $_helper = null;
	
	protected  static $_data = array();
	
	public function __construct() {
		if (!isset(self::$_helper)) {
			self::$_helper = t3lib_div::makeInstance('tx_typogento_configuration');
		}
	}
	
	public function getDocumentRoot() {
		// 
		if (!isset(self::$_data['document_root'])) {
			
			$value = self::$_helper->getValue(tx_typogento_configuration::EXTENSION_MANAGER, 'path');
			$path = realpath($value);
			
			if ($path === false) {
				tx_typogento_div::throwException('lib_document_root_not_valid_error', 
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
			
			$value = self::$_helper->getValue(tx_typogento_configuration::EXTENSION_MANAGER, 'url');
			$url = filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
			
			if ($url === false) {
				tx_typogento_div::throwException('lib_base_url_not_valid_error', 
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
		return self::$_helper->getValue(tx_typogento_configuration::EXTENSION_MANAGER, 'username');
	}
	
	public function getApiPassword() {
		return self::$_helper->getValue(tx_typogento_configuration::EXTENSION_MANAGER, 'password');
	}
	
	public function getWebsiteId() {
		return self::$_helper->getValue(tx_typogento_configuration::EXTENSION_MANAGER, 'website');
	}
}
?>
