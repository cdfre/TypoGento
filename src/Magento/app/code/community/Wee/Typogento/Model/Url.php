<?php 

/**
 * Typogento URL model overrides
 * 
 * 
 * @todo Not sure this is right implemented
 *
 */
class Wee_Typogento_Model_Url extends Mage_Core_Model_Url {
	
	/**
	 * Default Constructor
	 *
	 */
	public function _construct(){
		parent::_construct();
	}
	
	/**
	 * Build URL by requested path and parameters
	 * 
	 * 
	 * @todo Support for fragments
	 * 
	 * @param   string $path       Path to the route
	 * @param   array  $arameters  Parameters for the route
	 * @param   bool   $useDefault If set base class is used
	 * @return  string
	 */
	public function getUrl($path = null, $parameters = null, $useDefault = false) {
		// get typo3 helper
		$typo3 = Mage::helper('typogento/typo3');
		// 
		$url = parent::getUrl($path, $parameters);
		// check if typo3 is disabled or default url is requested
		if (!$typo3->isFrontendActive() || $useDefault){
			return $url;
		}
		
		// here we just collect the processed parameters 
		// in a flat data array to pass them to gettypolink()
		
		// collect route data
		$data = array(
			'route' => $this->getRouteName(),
			'controller' => $this->getControllerName(),
			'action' => $this->getActionName(),
		);
		// get route parameters only (ignores query params)
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
		// recreate link for typo3 frontend using link data
		$url = $this->getTypolink($data);

		// save last url in response for the _isurlinternal workaround
		$response = Mage::app()->getResponse();
		$response->lastUrl = $url;

		// return url
		return $url;
	}
	
	/**
	 * Retrieve route URL
	 * 
	 * If TYPO3 is activated this doesn't use 
	 * the store base url. If _direct is set 
	 * path is replaced by its target path.
	 *  
	 * @param string $routePath
	 * @param array $routeParams
	 *
	 * @return string
	 */
	public function getRouteUrl($path = null, $parameters = null) {
		// get typo3 helper
		$typo3 = Mage::helper('typogento/typo3');
		// check if typo3 is disabled or original url is requested
		if (!$typo3->isFrontendActive()) {
			return Mage_Core_Model_Url::getRouteUrl($path, $parameters);
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
		
		if (!is_null($path)) {
			$this->setRoutePath($path);
		}
		if (is_array($parameters)) {
			$this->setRouteParams($parameters, false);
		}
	
		$url = $this->getRoutePath($parameters);
		
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
	 * Generate TYPO3 frontend url
	 *
	 * @todo Maybe preserving the overriden query params in a typoscript register
	 * @param array $params
	 * @return string URL
	 */
	protected function getTypolink(array &$data = array()) {
		// get typo3 helper
		$typo3 = Mage::helper('typogento/typo3');
		// get typo3 router
		$router = $typo3->getRouter();
		// prepare environment
		$filter = $typo3->getRouteEnvironment();
		$filter->register('getVars', $_GET);
		$filter->register('queryString', $_SERVER['QUERY_STRING']);
		$filter->getVars = $data;
		$filter->queryString = t3lib_div::implodeArrayForUrl('', $filter->getVars, '', false, true);
		$target = $typo3->getRouteEnvironment();
		$target->register('getVars', $_GET);
		$target->register('queryString', $_SERVER['QUERY_STRING']);
		$target->getVars = array('tx_typogento' => $data);
		$target->queryString = t3lib_div::implodeArrayForUrl('', $target->getVars, '', false, true);
		// get url
		return $router->lookup(tx_typogento_router::ROUTE_SECTION_LINKS, $filter, $target);
	}
}