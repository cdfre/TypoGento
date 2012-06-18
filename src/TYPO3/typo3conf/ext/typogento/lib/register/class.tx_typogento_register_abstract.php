<?php 

/**
 * Abstract TypoScript frontend register
 * 
 * @see tx_typogento_observer::configArrayPostProc()
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
abstract class tx_typogento_register_abstract {
	
	/**
	 * @var tslib_fe
	 */
	protected $_frontend = null;
	
	/**
	 * @var tx_typogento_configuration
	 */
	protected $_configuration = null;
	
	/**
	 * @var array
	 */
	protected $_data = null;
	
	/**
	 * Constructor
	 * 
	 * @param tslib_fe $frontend
	 * @param unknown_type $key
	 */
	public function __construct(tslib_fe $frontend) {
		// member
		$this->_configuration = t3lib_div::makeInstance('tx_typogento_configuration');
		$this->_frontend = $frontend;
	}
	
	/**
	 * 
	 */
	public function preLoad() {
		// skip finished
		if ($this->_data != null) {
			return;
		}
		// pre load data
		$this->_onPreLoad();
		// register data
		$this->_register();
	}
	
	/**
	 * 
	 */
	public function postLoad() {
		// skip finished
		if ($this->_data != null) {
			return;
		}
		// post load data
		$this->_onPostLoad();
		// register data
		$this->_register();
	}
	
	/**
	 * Register data in the frontend
	 */
	protected function _register() {
		// skip unfinished
		if ($this->_data == null) {
			return;
		}
		// register data
		$this->_frontend->register += $this->_data;
	}
	
	protected abstract function _onPreLoad();
	
	protected abstract function _onPostLoad();
	
}

?>