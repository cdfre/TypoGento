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
	public function outputBody() {
		$content = implode('', (array)$this->_body);
		return $content;
	}
	
	/**
	 * Set Body
	 *
	 * @param string $content
	 * @param string $name
	 * @return $this
	 * @todo Check if still necessary
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
		
		// not longer necessary because of the rewriting of the app Model we can change Response Object everywhere
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
	 * 
	 * @return Zend_Controller_Response_Abstract
	 */
	public function setRedirect($url, $code = 302) {
		$url = \TYPO3\CMS\Core\Utility\GeneralUtility::locationHeaderUrl($url);
		return parent::setRedirect($url, $code);
	}
	
	/**
	 * Check if this is an Ajax response
	 * 
	 * @return bool True on an Ajax response otherwise false.
	 */
	public function isAjax() {
		// always respond with ajax on xml http requests
		$ajax = Mage::app()->getFrontController()->getRequest()->isXmlHttpRequest();
		// respond with ajax on content type 'application/json'
		if (!$ajax) {
			foreach ($this->_headers as $header) {
				if (strtolower($header['name']) != 'content-type') {
					continue;
				}
				$ajax = strtolower($header['value']) == 'application/json';
			}
		}
		// return result
		return $ajax;
	}
	
	/**
	 * Check if this is a HTTP 404 response
	 * 
	 * @return boolean False on HTTP 404 otherwise true.
	 */
	public function isAvailable() {
		// available per default
		$available = true;
		// check headers
		foreach ($this->_headers as $header) {
			if (strtolower($header['name']) != 'http/1.1') {
				continue;
			}
			$available = strtolower($header['value']) != '404 not found';
		}
		// return result
		return $available;
	}
}
