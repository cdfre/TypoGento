<?php 

/**
 * TypoGento route environment
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_environment {
	
	protected static $_stack = array();
	
	protected $_isInitialized = false;
	
	protected $_references = array();
	
	protected $_current = array();
	
	protected $_preserved = array();
	
	public function &__get($name) {
		return $this->_current[$name];
	}
	
	public function __set($name, $value) {
		$this->_current[$name] = $value;
	}
	
	public function register($name, &$value) {
		$this->_references[$name] = &$value;
		$this->_current[$name] = $value;
		$this->_preserved[$name] = $value;
	}
	
	public function initialize() {
		// skip
		if ($this->_isInitialized) {
			return;
		}
		// push stack
		self::$_stack[] = $this;
		// 
		foreach ($this->_current as $key => &$value) {
			$this->_references[$key] = $value;
		}
		// block initialize
		$this->_isInitialized = true;
	}
	
	public function deinitialize() {
		// skip
		if (!$this->_isInitialized) {
			return;
		}
		// check order
		if (end(self::$_stack) !== $this) {
			throw tx_typogento_div::exception('lib_unknown_error', array());
		}
		// 
		foreach ($this->_preserved as $key => &$value) {
			$this->_references[$key] = $value;
		}
		// pop stack
		array_pop(self::$_stack);
		// block deinitialize
		$this->_isInitialized = false;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_environment.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_environment.php']);
}

?>