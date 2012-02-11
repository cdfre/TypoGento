<?php 

/**
* TypoGento router
*
* Routes between TYPO3 Pages and Magento Route Paths.
* 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
*/
class tx_weetypogento_router implements t3lib_Singleton {
	
	/**
	 * 
	 * @var string
	 */
	const ROUTE_SECTION_LINKS = 'links';
	
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
	 * @see Wee_Typogento_Controller_Router, Wee_Typogento_Model_Url
	 * @param tx_weetypogento_routeBuilder $builder Builder to setup routing for the current page
	 * @return void
	 */
	public function __construct(tx_weetypogento_routeBuilder $builder = null) {
		if (!isset($builder)) {
			$builder = new tx_weetypogento_defaultRouteBuilder();
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
	public function lookup($section, tx_weetypogento_routeEnvironment $filter = null, tx_weetypogento_routeEnvironment $target = null) {
		// assert section exists in route tree
		if (!array_key_exists($section, $this->_sections)) {
			throw new InvalidArgumentException(sprintf('Unknown route section \'%s\'', $section));
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
		throw new Exception(sprintf('No active route was found in section \'%s\'', $section));
	}

	/**
	 * Add a route
	 * 
	 * Adds a route to the router into the specified section. 
	 * 
	 * @param tx_weetypogento_route $route The route to add
	 * @param string $section The route section for the added route 
	 */
	public function add($section, tx_weetypogento_route $route) {
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/routing/class.tx_weetypogento_router.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/routing/class.tx_weetypogento_router.php']);
}

?>
