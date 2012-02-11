<?php 

/**
 * TypoGento application
 * 
 * Currently we just override the request and response member.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Wee_Typogento_Model_App extends Mage_Core_Model_App {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Retrieve request object
	 *
	 * @return Wee_Typogento_Controller_Request
	 */
	public function getRequest() {
		if (empty($this->_request)) {
			$this->_request = new Wee_Typogento_Controller_Request();
		}
		return $this->_request;
	}
	
	/**
	 * Retrieve response object
	 *
	 * @return Wee_Typogento_Controller_Response
	 */
	public function getResponse() {
		if (empty($this->_response)) {
			$this->_response = new Wee_Typogento_Controller_Response();
			$this->_response->headersSentThrowsException = Mage::$headersSentThrowsException;
		}
		return $this->_response;
	}
}
