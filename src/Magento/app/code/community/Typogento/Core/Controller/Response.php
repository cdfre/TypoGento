<?php

/**
 * TypoGento response controller
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Core_Controller_Response extends Mage_Core_Controller_Response_Http {
	
	public $lastUrl = null;
	
	/**
	 * Echo the body segments
	 *
	 * @return void
	 */
	public function appendBody($output, $name = null) {
		
		$this->ajaxHandler($output);
		
		return parent::appendBody($output, $name);
	}
	
	/**
	 * Handle Ajax Requests
	 *
	 * @param string $output
	 */
	protected function ajaxHandler($output) {
		
		if (!Mage::app ()->getFrontController ()->getRequest ()->isXmlHttpRequest()) {
			return;
		}
		
		if ($GLOBALS['TSFE']->renderCharset) {
			header('Content-Type: text/html; charset='.$GLOBALS['TSFE']->renderCharset);
		} 
		
		echo $output;
		exit;
	}
	
	/**
	 * Echo the body segments
	 *
	 * @param boolean $returnBody
	 * @return void
	 */
	public function outputBody($returnBody = false) {
		
		$content = implode('', (array)$this->_body);
		
		if (!$returnBody) {
			$this->ajaxHandler($content);
		} else{
			
			return $content;
		}
	}
	
	/**
	 * Send the Response and exit
	 */
	public function sendResponse() {
		
		parent::sendResponse();
		
		if ($this->isRedirect()) {
			exit ();
		}
	}
	
	/**
	 * set Body
	 *
	 * @param string $content
	 * @param string $name
	 * @return $this
	 */
	public function setBody($content, $name = null) {
			// handle Checkout redirects
		if (strstr($content, 'paypal_standard_checkout') 
		|| strstr($content, 'clickandbuy_checkout')
		|| strstr($content, 'payone_checkout')
		|| strstr($content, 'moneybookers_checkout')) {
			echo $content;
			exit;
		}
		
		// not longer necessary because of the rewriting of the app Model we can change die Response Object everywhere
		//$this->ajaxHandler($content);
		return parent::setBody($content, $name);
	}
	
	/**
	 * Set redirect URL
	 *
	 * Sets Location header and response code. Forces replacement of any prior
	 * redirects.
	 *
	 * @param string $url
	 * @param int $code
	 * @return Zend_Controller_Response_Abstract
	 */
	public function setRedirect($url, $code = 302) {
		// set last URL for the _isUrlInternal workaround
		if ($url == Mage::app()->getStore()->getBaseUrl() && $this->lastUrl){
			$url = $this->lastUrl;
		}
		$this->canSendHeaders(true);
		#$this->setHeader ( 'Location', t3lib_div::locationHeaderUrl ( $url ), true )->setHttpResponseCode ( $code );
		$this->sendHeaders();
		$this->_isRedirect = true;
		
		header('Location: ' . t3lib_div::locationHeaderUrl($url));
		exit;
	}

}
