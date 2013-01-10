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
			// get environment data
			$data = $this->_collectEnvironmentData();
			// rewrite url
			$rewriter = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Core\\Rewriter');
			$rewriter->rewrite($url, $data);
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
	 * Collects the data for filter and target environments
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
}