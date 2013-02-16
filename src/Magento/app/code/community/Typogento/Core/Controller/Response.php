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
		// prefix url
		$url = \TYPO3\CMS\Core\Utility\GeneralUtility::locationHeaderUrl($url);
		// fix output of some controllers (e.g. checkout)
		$url = htmlspecialchars_decode($url);
		// default implementation
		return parent::setRedirect($url, $code);
	}
	
	/**
	 * Is the response a Javascript XmlHttpResponse
	 * 
	 * @return boolean
	 */
	public function isXmlHttpResponse() {
		// true on xml http requests
		$xmlHttpResponse = Mage::app()->getFrontController()->getRequest()->isXmlHttpRequest();
		// check headers otherwise
		if (!$xmlHttpResponse) {
			foreach ($this->_headers as $header) {
				// checks content type
				if (strtolower($header['name']) != 'content-type') {
					continue;
				}
				// use last entry
				$xmlHttpResponse = strtolower($header['value']) == 'application/json';
			}
		}
		// return result
		return $xmlHttpResponse;
	}
	
	/**
	 * Is the response not a HTTP 404
	 * 
	 * @return boolean
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
