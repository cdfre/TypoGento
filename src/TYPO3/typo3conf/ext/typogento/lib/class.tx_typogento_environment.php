<?php 

/**
 * TypoGento route environment
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_environment {
	
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
		foreach ($this->_current as $key => &$value) {
			$this->_references[$key] = $value;
		}
	}
	
	public function deinitialize() {
		foreach ($this->_preserved as $key => &$value) {
			$this->_references[$key] = $value;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_environment.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_environment.php']);
}

?>