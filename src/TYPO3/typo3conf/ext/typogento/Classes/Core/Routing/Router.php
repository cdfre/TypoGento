<?php 

namespace Tx\Typogento\Core\Routing;

use \Tx\Typogento\Utility\GeneralUtility;
use \Tx\Typogento\Configuration\TypoScript\Routing\RouteBuilder;
use \Tx\Typogento\Core\Environment;

/**
 * Routes between TYPO3 Pages and Magento Route Paths.
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Router implements \TYPO3\CMS\Core\SingletonInterface {
	
	/**
	 * @var string
	 */
	const ROUTE_SECTION_RENDER = 'render';
	
	/**
	 * @var string
	 */
	const ROUTE_SECTION_DISPATCH = 'dispatch';
	
	/**
	 * @var array
	 */
	protected $sections = array();
	
	/**
	 * Creates the router and setup its routes.
	 *
	 * @see typogento_Controller_Router, typogento_Model_Url
	 * @param RouteBuilderInterface $builder Builder to setup the routes
	 * @return void
	 */
	public function __construct(RouteBuilderInterface $builder = null) {
		// uses the default route builder if not set
		if (!isset($builder)) {
			$builder = new RouteBuilder();
		}
		// build the routes
		$builder->build($this);
	}
	
	/**
	 * Find the matching route.
	 * 
	 * @param string $section The route section to search in
	 * @param Environment $filter The filter environment to initialize before
	 * @throws Exception If no matching route was found or the given route section doesn't exist
	 * @return void
	 */
	public function lookup($section, Environment $filter = null) {
		// assert section exists in route tree
		if (!array_key_exists($section, $this->sections)) {
			throw new Exception(sprintf('The route section "%s" is not valid', $section), 1356843514);
		}
		// get priority slots from section
		$slots = &$this->sections[$section];
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
					return $route;
				}
			}
		}
		// deinitialize filter environment
		if (isset($filter)) {
			$filter->deinitialize();
		}
		//
		throw new Exception(sprintf('The route section "%s" has no matching entry', $section), 1356843144);
	}
	
	/**
	 * Process a route.
	 * 
	 * @param Route $route The route to process
	 * @param Environment $target The target environment to initialize before
	 * @return mixed The processing result
	 */
	public function process(Route $route, Environment $target = null) {
		// initialize target environment
		if (isset($target)) {
			$target->initialize();
		}
		// process matching route
		$result = $route->getHandler()->process();
		// deinitialize target environment
		if (isset($target)) {
			$target->deinitialize();
		}
		return $result;
	}

	/**
	 * Adds a route.
	 * 
	 * @param Route $route The route to add
	 * @param string $section The route section
	 * @return void
	 */
	public function add($section, Route $route) {
		// check if section not exists
		if (!array_key_exists($section, $this->sections)) {
			$this->sections[$section] = array();
		}
		$priority = $route->getPriority();
		
		if (!isset($this->sections[$section][$priority])) {
			$this->sections[$section][$priority] = array();
		}
		$this->sections[$section][$priority][] = $route;
		krsort($this->sections[$section]);
	}
}
?>
