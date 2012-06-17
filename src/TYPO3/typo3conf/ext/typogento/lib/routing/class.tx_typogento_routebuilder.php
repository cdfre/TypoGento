<?php 

/**
 * TypoGento route builder interface
 * 
 * Interface for a route builder creating routes for the TypoGento router.
 *  
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
interface tx_typogento_routeBuilder {
	
	/**
	 * Build the routes
	 * 
	 * @param tx_typogento_router $router The router to setup
	 */
	function build(tx_typogento_router $router);
}

/**
 * TypoGento default route builder
 * 
 * Builds the TypoGento router using TypoScript.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_defaultRouteBuilder implements tx_typogento_routeBuilder {
	
	/**
	 * Build the routes
	 * 
	 * Uses the TypoScript setup and caches the FlexForm route path.
	 * 
	 * @see tx_typogento_routeBuilder::build()
	 */
	public function build(tx_typogento_router $router) {
		// get configuration helper
		$helper = t3lib_div::makeInstance('tx_typogento_configuration');
		// get routes setup
		$routes = &$helper->get('routes.', array());
		// skip if routes setup is empty
		if (count($routes) < 1) {
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
	}
	
	/**
	 * Create a single route from TypoScript
	 * 
	 * @param array $config TypoScript setup
	 * @throws InvalidArgumentException If something is missing in the TypoScript setup
	 */
	protected function _buildRoute(array &$typoscript) {
		if (!isset($typoscript['filter.'])) {
			tx_typogento_div::throwException('lib_missing_route_filter_error', 
				array(print_r($typoscript, true))
			);
		}
	
		if (!isset($typoscript['target.'])) {
			tx_typogento_div::throwException('lib_missing_route_target_error', 
				array(print_r($typoscript, true))
			);
		}
		
		$priority = $this->_buildValue('priority', $typoscript, 0);
	
		$filter = new tx_typogento_typoscriptRouteFilter($typoscript['filter.']);
		$handler = new tx_typogento_typolinkRouteHandler($typoscript['target.']);
		
		return new tx_typogento_route($filter, $handler, $priority);
	}
	
	/**
	 * Helper function
	 * 
	 * @param unknown_type $key
	 * @param array $conf
	 * @param unknown_type $default
	 */
	protected function _buildValue($key, array &$typoscript, $default) {
		$cObj = tx_typogento_div::getContentObject();
		return isset($typoscript[$key.'.'])
			? trim($cObj->stdWrap($typoscript['$key'], $typoscript[$key.'.']))
			: isset($typoscript[$key])
				? trim($typoscript[$key])
				: $default;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/routing/class.tx_typogento_routebuilder.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/routing/class.tx_typogento_routebuilder.php']);
}

?>
