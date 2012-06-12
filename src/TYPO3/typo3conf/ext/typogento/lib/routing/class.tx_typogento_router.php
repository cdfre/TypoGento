<?php 

/**
* TypoGento router
*
* Routes between TYPO3 Pages and Magento Route Paths.
* 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
*/
class tx_typogento_router implements t3lib_Singleton {
	
	/**
	 * 
	 * @var string
	 */
	const ROUTE_SECTION_RENDER = 'render';
	
	/**
	 * 
	 * @var string
	 */
	const ROUTE_SECTION_DISPATCH = 'dispatch';
	
	/**
	 * 
	 * @var array
	 */
	protected $_sections = array();
	
	/**
	 * Create TypoGento router
	 *
	 * @see typogento_Controller_Router, typogento_Model_Url
	 * @param tx_typogento_routeBuilder $builder Builder to setup routing for the current page
	 * @return void
	 */
	public function __construct(tx_typogento_routeBuilder $builder = null) {
		if (!isset($builder)) {
			$builder = new tx_typogento_defaultRouteBuilder();
		}
		

		$builder->build($this);
	}
	
	/**
	 * Lookup the active route and process it
	 * 
	 * @param unknown_type $section The route section to search in
	 * @param array $source The source route set to lookup
	 * @throws InvalidArgumentException If the given route section doesn't exist
	 * @throws Exception If no matching route was found
	 */
	public function lookup($section, tx_typogento_environment $filter = null, tx_typogento_environment $target = null) {
		// assert section exists in route tree
		if (!array_key_exists($section, $this->_sections)) {
			tx_typogento_div::throwException('lib_route_section_not_valid_error', 
				array($section)
			);
		}
		// get priority slots from section
		$slots = &$this->_sections[$section];
		// initialize filter environment
		if (isset($filter)) {
			$filter->initialize();
		}
		// iterate priority slots
		foreach ($slots as $slot) {
			// iterate priority slot stack
			foreach ($slot as $route) {
				// check if route filter match
				if ($route->getFilter()->match()) {
					// deinitialize filter environment
					if (isset($filter)) {
						$filter->deinitialize();
					}
					//
					if (isset($target)) {
						$target->initialize();
					}
					// process matching route
					$result = $route->getHandler()->process();
					//
					if (isset($target)) {
						$target->deinitialize();
					}
					return $result;
				}
			}
		}
		// deinitialize filter environment
		if (isset($filter)) {
			$filter->deinitialize();
		}
		//
		if (isset($target)) {
			$target->deinitialize();
		}
		tx_typogento_div::throwException('lib_active_route_not_found_error', 
			array($section)
		);
	}

	/**
	 * Add a route
	 * 
	 * Adds a route to the router into the specified section. 
	 * 
	 * @param tx_typogento_route $route The route to add
	 * @param string $section The route section for the added route 
	 */
	public function add($section, tx_typogento_route $route) {
		// check if section not exists
		if (!array_key_exists($section, $this->_sections)) {
			$this->_sections[$section] = array();
		}
		$priority = $route->getPriority();
		
		if (!isset($this->_sections[$section][$priority])) {
			$this->_sections[$section][$priority] = array();
		}
		$this->_sections[$section][$priority][] = $route;
		krsort($this->_sections[$section]);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/routing/class.tx_typogento_router.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/routing/class.tx_typogento_router.php']);
}

?>
