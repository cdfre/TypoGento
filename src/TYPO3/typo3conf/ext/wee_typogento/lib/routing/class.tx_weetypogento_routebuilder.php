<?php 

/**
 * TypoGento route builder interface
 * 
 * Interface for a route builder creating routes for the TypoGento router.
 *  
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
interface tx_weetypogento_routeBuilder {
	
	/**
	 * Build the routes
	 * 
	 * @param tx_weetypogento_router $router The router to setup
	 */
	function build(tx_weetypogento_router $router);
}

/**
 * TypoGento default route builder
 * 
 * Builds the TypoGento router using TypoScript.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_weetypogento_defaultRouteBuilder implements tx_weetypogento_routeBuilder {
	
	/**
	 * @see tx_weetypogento_routeBuilder::build()
	 */
	public function build(tx_weetypogento_router $router) {
		// get configuration helper
		$helper = t3lib_div::makeInstance('tx_weetypogento_configurationHelper');
		// get plugin setup
		$setup = $helper->getSection(tx_weetypogento_configurationHelper::TYPOSCRIPT_SETUP);
		// get routes setup
		$routes = &$setup['routes.'];
		// skip if routes setup is empty
		if (!isset($routes)) {
			return;
		}
		// build routes
		foreach($routes as $key => &$conf) {
			// skip if no route setup exist
			if (strpos($key, '.') === false) {
				continue;
			}
			// build route
			$route = $this->_buildRoute($conf);
			// add route to router
			$router->add($conf['section'], $route);
		}
		
		if (!isset($GLOBALS['TSFE']->config['tx_weetypogento'])) {
			$GLOBALS['TSFE']->config['tx_weetypogento'] = $this->_getRouteSegments($GLOBALS['TSFE']);
		}
	}
	
	/**
	 * Get route set from FlexForm
	 * 
	 * Extracts the route set from a FlexForm array if set.
	 * 
	 * @param array $flexform
	 * @return array
	 * @throws Exception
	 */
	protected function _buildRouteSegments(array $flexform) {
		$field = 'main';
		$view = &tx_weetypogento_div::getFFvalue($flexform, 'show', $field);
		$segments = null;
	
		if (!$view) {
			tx_weetypogento_div::throwException('lib_view_type_not_set_error');
		}
	
		switch ($view) {
			case "SINGLEPRODUCT":
				$product = &tx_weetypogento_div::getFFvalue($flexform, 'product_id', $field);
				$segments = array(
					'route'=>'catalog', 'controller'=>'product', 
					'action'=>'view', 'id' => $product
				);
				break;
			case "PRODUCTLIST":
				$category = &tx_weetypogento_div::getFFvalue($flexform, 'category_id', $field);
				$segments = array(
					'route'=>'catalog', 'controller'=>'category', 
					'action'=>'view', 'id' => $category
				);
				break;
			case "USER":
				$segments = array(
					'route'=> tx_weetypogento_div::getFFvalue($flexform, 'route', $field),
					'controller'=> tx_weetypogento_div::getFFvalue($flexform, 'controller', $field),
					'action'=> tx_weetypogento_div::getFFvalue($flexform, 'action', $field)
				);
				break;
			default:
				tx_weetypogento_div::throwException('lib_view_type_not_valid_error', 
					array($view)
				);
		}
	
		return $segments;
	}
	
	/**
	 * Get Route from TypoScript
	 * 
	 * Creates a TypoGento route from TypoScript setup 
	 * like used in 'plugin.tx_weetypogento.routes'.
	 * 
	 * @param array $config TypoScript setup for a TypoGento route
	 * @throws InvalidArgumentException If something is missing in the TypoScript setup
	 */
	protected function _buildRoute(array &$typoscript) {
		if (!isset($typoscript['filter.'])) {
			tx_weetypogento_div::throwException('lib_missing_route_filter_error', 
				array(print_r($typoscript, true))
			);
		}
	
		if (!isset($typoscript['target.'])) {
			tx_weetypogento_div::throwException('lib_missing_route_target_error', 
				array(print_r($typoscript, true))
			);
		}
		
		$priority = $this->_buildValue('priority', $typoscript, 0);
	
		$filter = new tx_weetypogento_typoscriptRouteFilter($typoscript['filter.']);
		$handler = new tx_weetypogento_typolinkRouteHandler($typoscript['target.']);
		
		return new tx_weetypogento_route($filter, $handler, $priority);
	}
	
	/**
	 * Get route 
	 * 
	 * @return boolean
	 */
	protected function _getRouteSegments(tslib_fe $page) {
		$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
			'pi_flexform',
			'tt_content',
			'pid=\'' . $page->id.'\' AND list_type=\'wee_typogento_pi1\' ' . $page->sys_page->enableFields('tt_content'),
			'sorting'
		);

		if (!isset($row['pi_flexform'])) {
			return null;
		}
		
		$flexform = t3lib_div::xml2array($row['pi_flexform']);
		
		if (!isset($flexform['data']['main']['lDEF']['show']['vDEF'])) {
			return null;
		}
		
		return $this->_buildRouteSegments($flexform);
	}
	
	/**
	 * Helper function
	 * 
	 * @param unknown_type $key
	 * @param array $conf
	 * @param unknown_type $default
	 */
	protected function _buildValue($key, array &$typoscript, $default) {
		$cObj = tx_weetypogento_div::getContentObject();
		return isset($typoscript[$key.'.'])
			? trim($cObj->stdWrap($typoscript['$key'], $typoscript[$key.'.']))
			: isset($typoscript[$key])
				? trim($typoscript[$key])
				: $default;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/routing/class.tx_weetypogento_routebuilder.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/routing/class.tx_weetypogento_routebuilder.php']);
}

?>
