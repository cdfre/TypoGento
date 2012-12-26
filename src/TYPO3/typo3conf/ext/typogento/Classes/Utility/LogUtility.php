<?php 

namespace Tx\Typogento\Utility;

/**
 * Wrapper for the Logger
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class LogUtility {
	
	/**
	 * 
	 * @var \TYPO3\CMS\Core\Log\Logger
	 */
	protected static $logger = null;
	
	protected static function initialize() {
		if (self::$logger === null) {
			self::$logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
		}
	}
	
	/**
	 * Adds a log record.
	 *
	 * @param integer $level Log level.
	 * @param string $message Log message.
	 * @param array $data Additional data to log
	 * @return mixed
	 */
	public static function log($level, $message, array $data = array()) {
		self::initialize();
		return self::$logger->log($level, $message, data);
	}
	
	/**
	 * Shortcut to log an ERROR record.
	 *
	 * @param string $message Log message.
	 * @param array $data Additional data to log
	 * @return \TYPO3\CMS\Core\Log\Logger $this
	 */
	public static function error($message, array $data = array()) {
		self::initialize();
		return self::$logger->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $message, $data);
	}
	
	/**
	 * Shortcut to log a WARNING record.
	 *
	 * @param string $message Log message.
	 * @param array $data Additional data to log
	 * @return \TYPO3\CMS\Core\Log\Logger $this
	 */
	public function warning($message, array $data = array()) {
		return self::$logger->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, $message, $data);
	}
	
	/**
	 * Shortcut to log a NOTICE record.
	 *
	 * @param string $message Log message.
	 * @param array $data Additional data to log
	 * @return \TYPO3\CMS\Core\Log\Logger $this
	 */
	public function notice($message, array $data = array()) {
		return self::$logger->log(\TYPO3\CMS\Core\Log\LogLevel::NOTICE, $message, $data);
	}
	
	/**
	 * Shortcut to log an INFORMATION record.
	 *
	 * @param string $message Log message.
	 * @param array $data Additional data to log
	 * @return \TYPO3\CMS\Core\Log\Logger $this
	 */
	public function info($message, array $data = array()) {
		return self::$logger->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, $message, $data);
	}
	
	/**
	 * Shortcut to log a DEBUG record.
	 *
	 * @param string $message Log message.
	 * @param array $data Additional data to log
	 * @return \TYPO3\CMS\Core\Log\Logger $this
	 */
	public static function debug($message, array $data = array()) {
		self::initialize();
		return self::$logger->log(\TYPO3\CMS\Core\Log\LogLevel::DEBUG, $message, $data);
	}
}
?>