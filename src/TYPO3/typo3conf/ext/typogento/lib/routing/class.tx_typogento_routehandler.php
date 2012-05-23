<?php 

/**
 * TypoGento route handler interface
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
interface tx_typogento_routeHandler {

	function process();
}

/**
 * TypoGento TypoScript route handler
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_typolinkRouteHandler implements tx_typogento_routeHandler {
	
	/**
	 * @var array
	 */
	protected $_conf = null;
	
	/**
	 * @var array
	 */
	protected $_postProcessing = null;
	
	/**
	 * @var array
	 */
	protected $_preservedHooks = null;
	
	/**
	 * 
	 * Enter description here ...
	 * @param tslib_cObj $cObj
	 * @param array $conf
	 */
	public function __construct(array $conf) {
		$this->_conf = $conf;
		
		$this->_postProcessing = array('obj' => $this, 'method' => '_postProcess');
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function process() {
		$cObj = tx_typogento_div::getContentObject();
		$hooks = &$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typoLink_PostProc'];
		$persistent = &$GLOBALS['T3_VAR']['callUserFunction'];
		$exception = null;
		// replace typolink post processing hooks
		$this->_preservedHooks = $this->_replace($hooks, array('tx_typogento_typolinkRouteHandler.postProcessor'));
		$this->_replace($persistent['tx_typogento_typolinkRouteHandler.postProcessor'], $this->_postProcessing);
		// render typolink
		try {
			$cObj->typolink('', $this->_conf);
		} catch (Exception $e) {
			$exception = $e;
		}
		// restore typolink post processing hooks
		$this->_replace($hooks, $this->_preservedHooks);
		$this->_replace($persistent['tx_typogento_typolinkRouteHandler.postProcessor'], null);
		// check if any exception was thrown
		if (isset($exception)) {
			throw $exception;
		}
		// get rendered url
		$url = $cObj->lastTypoLinkUrl;
		// return result
		return $url;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @internal
	 */
	public function _postProcess(array &$params, tslib_cObj $cObj) {
		$type = $params['finalTagParts']['TYPE'];
		
		if ($type === 'url') { // typo3 request -> magento action
			// get last url components
			$components = parse_url($cObj->lastTypoLinkUrl);
			// provides .addQueryString and .additionalParams conf
			$components['query'] .= $this->_conf['addQueryString'] ? $cObj->getQueryArguments($this->_conf['addQueryString.']) : '';
			$components['query'] .= isset($this->_conf['additionalParams.'])
				? trim($cObj->stdWrap($this->_conf['additionalParams'], $this->_conf['additionalParams.']))
				: trim($this->_conf['additionalParams']);
			if ($components['query'] == '&' || substr($components['query'], 0, 1) != '&') {
				$components['query'] = '';
			}
			// set url using only path and query
			$cObj->lastTypoLinkUrl = $components['path'].'?'.trim($components['query'], '&');
		} elseif ($type === 'page') { // magento request -> typo3 page
			if (!isset($this->_preservedHooks)) {
				return;
			}
			
			foreach ($this->_preservedHooks as $hook) {
				t3lib_div::callUserFunction($hook, $params, $cObj);
			}
		} else {
			tx_typogento_div::throwException('lib_unsupported_link_type_error', 
				array($type)
			);
		}
	}
	
	protected function &_replace(&$source, $replacement) {
		$preserved = $source;
		$source = $replacement;
		return $preserved;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/routing/class.tx_typogento_routehandler.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/routing/class.tx_typogento_routehandler.php']);
}

?>