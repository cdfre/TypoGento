<?php 

namespace Tx\Typogento\Utility;

use Tx\Typogento\Configuration\Exception;
use Tx\Typogento\Configuration\ConfigurationManager;

/**
 * Configuration utility
 * 
 * @author Artus Kolanowsi <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class ConfigurationUtility {
	
	protected static $configuration = null;
	
	protected static $cache = array();
	
	protected static function initialize() {
		//
		if (!isset(self::$configuration)) {
			self::$configuration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Configuration\\ConfigurationManager');
		}
	}
	
	public static function getDocumentRoot() {
		// 
		self::initialize();
		// 
		if (!isset(self::$cache['document_root'])) {
			
			$value = self::$configuration->get('path', '', ConfigurationManager::EXTENSION);
			$path = realpath($value);
			
			if ($path === false) {
				throw new \Exception(sprintf('The document root "%s" is not valid.', $value), 1356929671);
			}
			
			self::$cache['document_root'] = $path;
		}
		//
		return self::$cache['document_root'];
	}
	
	public static function getBaseUrl() {
		//
		self::initialize();
		//
		if (!isset(self::$cache['base_url'])) {
			//
			$value = self::$configuration->get('url', '', ConfigurationManager::EXTENSION);
			$url = filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
			//
			if ($url === false) {
				throw new \Exception(sprintf('The base URL "%s" is not valid', $value), 1356929744);
			}
			//
			$components = parse_url($url);
			//
			$components['port'] = trim($components['port']) === ''
				? '' : ':'.$components['port'];
			$components['scheme'] = trim($components['scheme']) === ''
				? '' : $components['scheme'].'://';
			$components['path'] = rtrim($components['path'], '/').'/';
			//
			self::$cache['base_url'] = $components['scheme'].$components['host'].$components['port'].$components['path'];
		}
		//
		return self::$cache['base_url'];
	}
	
	public static function getApiAccount() {
		//
		self::initialize();
		//
		return self::$configuration->get('user', '', ConfigurationManager::EXTENSION);
	}
	
	public static function getApiPassword() {
		//
		self::initialize();
		//
		return self::$configuration->get('password', '', ConfigurationManager::EXTENSION);
	}
	
	public static function getWebsiteId() {
		//
		self::initialize();
		//
		return self::$configuration->get('website', -1, ConfigurationManager::EXTENSION);
	}
}
?>