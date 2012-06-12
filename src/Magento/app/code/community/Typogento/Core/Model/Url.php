<?php 

/**
 * URL model overrides
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Core_Model_Url extends Mage_Core_Model_Url {
	
	/**
	 * Build URL by requested path and parameters
	 * 
	 * @todo Support URL fragments
	 * 
	 * @param string $path Path to the route
	 * @param array $arameters Parameters for the route
	 * 
	 * @return  string
	 */
	public function getUrl($path = null, $parameters = null) {
		// get typo3 helper
		$typo3 = Mage::helper('typogento/typo3');
		// it's neccessary in both cases to process the passed arguments
		$url = parent::getUrl($path, $parameters);
		// check if typo3 is disabled or default behaviour is requested
		if ($typo3->isFrontendActive() 
			&& !$this->_getData('force_default_behaviour')) {
			// rerender link for typo3 frontend
			$url = $this->_getTypolink();
			// save last url in response for the _isurlinternal workaround
			$response = Mage::app()->getResponse();
			$response->lastUrl = $url;
		}
		// return url
		return $url;
	}
	
	/**
	 * Rebuild URL to handle the case when session ID was changed
	 *
	 * @todo Support URL rebuilds
	 * 
	 * @param string $url
	 * 
	 * @return string
	 */
	public function getRebuiltUrl($url) {
		// get typo3 helper
		$typo3 = Mage::helper('typogento/typo3');
		// check if typo3 is disabled or default behaviour is requested
		if (!$typo3->isFrontendActive() 
			|| $this->_getData('force_default_behaviour')) {
			$url = parent::getRebuiltUrl($url);
		}
		// return result
		return $url;
	}
	
	/**
	 * Retrieve route URL
	 * 
	 * If TYPO3 is activated this doesn't use the store base url. 
	 * If _direct is set path is replaced by its target path.
	 *  
	 * @param string $routePath
	 * @param array $routeParams
	 *
	 * @return string
	 */
	public function getRouteUrl($path = null, $parameters = null) {
		// get typo3 helper
		$typo3 = Mage::helper('typogento/typo3');
		// check if typo3 is disabled or default behaviour is requested
		if (!$typo3->isFrontendActive() 
			|| $this->_getData('force_default_behaviour')) {
			return parent::getRouteUrl($path, $parameters);
		}
		// unset previous route parameters
		$this->unsetData('route_params');
		// resolve url in _direct if set to get full qualified urls
		if (isset($parameters['_direct'])) {
			// get current store for rewrite
			$store = Mage::app()->getStore()->getId();
			// rewrite route using _direct
			$rewrite = Mage::getModel('core/url_rewrite');
			$rewrite->setStoreId($store);
			$rewrite->loadByRequestPath($parameters['_direct']);
			// remove processed parameter
			unset($parameters['_direct']);
			// set rewritten path
			$path = $rewrite->getTargetPath();
		}
		// 
		if (!is_null($path)) {
			$this->setRoutePath($path);
		}
		if (is_array($parameters)) {
			$this->setRouteParams($parameters, false);
		}
		// 
		$url = $this->getRoutePath($parameters);
		// 
		return $url;
	}
	
	/**
	 * Check if users originated URL is one of the domain URLs assigned to stores
	 *
	 * @return boolean
	 */
	public function isOwnOriginUrl() {
		// empty array for valid domains and the refered domain to check for
		$domains = array();
		$referer = parse_url(Mage::app()->getFrontController()->getRequest()->getServer('HTTP_REFERER'), PHP_URL_HOST);
		// itereate stores
		foreach (Mage::app()->getStores() as $store) {
			$domains[] = parse_url($store->getBaseUrl(), PHP_URL_HOST);
			$domains[] = parse_url($store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true), PHP_URL_HOST);
			// using additional domain related settings from typogento
			$valiate = Mage::helper('typogento/typo3')->getBackendBaseUrl();
			// and put them into the valid domains
			if (isset($valiate)) {
				$domains[] = parse_url($valiate, PHP_URL_HOST);
			}
		}
		// remove duplicates
		$domains = array_unique($domains);
		// check if referer is in the valid domains
		return (empty($referer) || in_array($referer, $domains));
	}
	
	/**
	 * Initialize object
	 */
	protected function _construct() {
		parent::_construct();
	}
	
	/**
	 * Collect URL data for rendering TYPO3 frontend URL
	 * 
	 * @see _getTypolink()
	 * 
	 * @return array
	 */
	protected function _getTypolinkData() {
		// collect route data
		$data = array(
			'route' => $this->getRouteName(),
			'controller' => $this->getControllerName(),
			'action' => $this->getActionName()
		);
		// get route parameters only (ignore query parameters)
		$parameters = $this->getRouteParams();
		// merge route parameters if set
		if (is_array($parameters)) {
			$parameters = array_filter($parameters);
			$data = array_merge($data, $parameters);
		}
		$parameters = $this->getQueryParams();
		// merge query parameters if set
		if (is_array($parameters)) {
			$parameters = array_filter($parameters);
			$data = array_merge($data, $parameters);
		}
		// return result
		return $data;
	}
	
	/**
	 * Render TYPO3 frontend URL
	 *
	 * @todo Maybe preserving the overriden query params in a typoscript register
	 * 
	 * @return string
	 */
	protected function _getTypolink() {
		// get typolink data
		$data = $this->_getTypolinkData();
		// get typo3 helper
		$typo3 = Mage::helper('typogento/typo3');
		// get typo3 router
		$router = $typo3->getRouter();
		// prepare filter environment
		$filter = $typo3->getEnvironment();
		$filter->register('getVars', $_GET);
		$filter->register('queryString', $_SERVER['QUERY_STRING']);
		$filter->getVars = $data;
		$filter->queryString = t3lib_div::implodeArrayForUrl('', $filter->getVars, '', false, true);
		// prepare target environment
		$target = $typo3->getEnvironment();
		$target->register('getVars', $_GET);
		$target->register('queryString', $_SERVER['QUERY_STRING']);
		$target->getVars = array('tx_typogento' => $data);
		$target->queryString = t3lib_div::implodeArrayForUrl('', $target->getVars, '', false, true);
		// render url
		$url = $router->lookup(tx_typogento_router::ROUTE_SECTION_RENDER, $filter, $target);
		// return result
		return $url;
	}
}