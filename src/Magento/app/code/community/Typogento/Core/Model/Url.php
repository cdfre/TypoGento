<?php 

/**
 * URL model overrides
 *
 * @author Artus Kolanowski <artus@ionoi.net>
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
	 * @return string
	 */
	public function getUrl($path = null, $parameters = null) {
		// it's neccessary in both cases to process the passed arguments
		$url = parent::getUrl($path, $parameters);
		// check if typo3 is disabled or default behaviour is requested
		if (Mage::helper('typogento_core/typo3')->isFrontendActive() 
			&& !$this->_getData('force_default_behaviour')) {
			// get router
			$router = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Core\\Routing\\Router');
			// get environment data
			$data = $this->_collectEnvironmentData();
			// build filter environment
			$filter = $this->_buildFilterEnvironment($data);
			// build filter environment
			$target = $this->_buildTargetEnvironment($data);
			// lookup matching route
			$route = $router->lookup(\Tx\Typogento\Core\Routing\Router::ROUTE_SECTION_RENDER, $filter);
			// rebuild url
			$rebuild = $router->process($route, $target);
			// log debug
			\Tx\Typogento\Utility\LogUtility::debug(
				sprintf(
					'[Routing] Rewrite URL "%s" to "%s" using render route "%s".', 
					urldecode($url), urldecode($rebuild), $route->getId()
				), 
				$data
			);
			// save last url in response for the _isurlinternal workaround
			$response = Mage::app()->getResponse();
			$response->lastUrl = $rebuild;
			// replace url
			$url = $rebuild;
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
		// check if typo3 is disabled or default behaviour is requested
		if (!Mage::helper('typogento_core/typo3')->isFrontendActive() 
			|| $this->_getData('force_default_behaviour')) {
			$url = parent::getRebuiltUrl($url);
		}
		// return result
		return $url;
	}
	
	/**
	 * Retrieve the route URL
	 * 
	 * If TYPO3 is activated this doesn't use the store base url. 
	 * If _direct is set path is replaced by its target path.
	 *  
	 * @param string $path
	 * @param array $params
	 *
	 * @return string
	 */
	public function getRouteUrl($path = null, $parameters = null) {
		// check if typo3 is disabled or default behaviour is requested
		if (!Mage::helper('typogento_core/typo3')->isFrontendActive() 
			|| $this->_getData('force_default_behaviour')) {
			return parent::getRouteUrl($path, $parameters);
		}
		// unset previous route parameters
		$this->unsetData('route_params');
		// try to resolve url for _direct parameters using url rewrites
		if (isset($parameters['_direct'])) {
			// get current store
			$store = Mage::app()->getStore()->getId();
			// load rewrite
			$rewrite = Mage::getModel('core/url_rewrite');
			$rewrite->setStoreId($store);
			$rewrite->loadByRequestPath($parameters['_direct']);
			// get rewriten path
			$path = $rewrite->getTargetPath();
			// remove _direct parameter on success
			if (!is_null($path)) {
				unset($parameters['_direct']);
			}
		}
		// reset path and parameters
		if (!is_null($path)) {
			$this->setRoutePath($path);
		}
		if (is_array($parameters)) {
			$this->setRouteParams($parameters, false);
		}
		// don't use base url
		$url = $this->getRoutePath($parameters);
		// return result
		return $url;
	}
	
	/**
	 * Checks if users originated URL is one of the domain URLs assigned to stores
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
			$valiate = Mage::helper('typogento_core/typo3')->getBackendBaseUrl();
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
	 * Collects the data for filter and target environment
	 * 
	 * @return array The colltected data
	 */
	protected function _collectEnvironmentData() {
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
	 * Builds the filter environment
	 * 
	 * @param array $data The environment data
	 * @return \Tx\Typogento\Core\Environment
	 */
	protected function _buildFilterEnvironment(&$data) {
		// prepare filter environment
		$filter = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Core\\Environment');
		$filter->register('getVars', $_GET);
		$filter->register('queryString', $_SERVER['QUERY_STRING']);
		$filter->getVars = $data;
		$filter->queryString = \TYPO3\CMS\Core\Utility\GeneralUtility::implodeArrayForUrl('', $filter->getVars, '', false, true);
		// return result
		return $filter;
	}
	
	/**
	 * Builds the target environment
	 *
	 * @param array $data The environment data
	 * @return \Tx\Typogento\Core\Environment
	 */
	protected function _buildTargetEnvironment(&$data) {
		// prepare target environment
		$target = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Core\\Environment');
		$target->register('getVars', $_GET);
		$target->register('queryString', $_SERVER['QUERY_STRING']);
		$target->getVars = array('tx_typogento' => $data);
		$target->queryString = \TYPO3\CMS\Core\Utility\GeneralUtility::implodeArrayForUrl('', $target->getVars, '', false, true);
		// return result
		return $target;
	}
}