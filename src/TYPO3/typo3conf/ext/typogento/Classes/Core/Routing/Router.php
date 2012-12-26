<?php 

namespace Tx\Typogento\Core\Routing;

use \Tx\Typogento\Utility\GeneralUtility;
use \Tx\Typogento\Configuration\TypoScript\Routing\RouteBuilder;
use \Tx\Typogento\Core\Environment;

/**
* Basic router
*
* Routes between TYPO3 Pages and Magento Route Paths.
* 
* @author Artus Kolanowski <artus@ionoi.net>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
*/
class Router implements \TYPO3\CMS\Core\SingletonInterface {
	
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
	protected $sections = array();
	
	/**
	 * Constructor
	 *
	 * @see typogento_Controller_Router, typogento_Model_Url
	 * @param RouteBuilderInterface $builder Builder to setup routing for the current page
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
	 * Lookup the active route and process it
	 * 
	 * @param string $section The route section to search in
	 * @param array $source The source route set to lookup
	 * @throws Exception If no matching route was found or the given route section doesn't exist
	 * @return void
	 */
	public function lookup($section, Environment $filter = null, Environment $target = null) {
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
			}
		}
		// deinitialize filter environment
		if (isset($filter)) {
			$filter->deinitialize();
		}
		// deinitialize target environment
		if (isset($target)) {
			$target->deinitialize();
		}
		//
		throw new Exception(sprintf('The route section "%s" has no matching entry', $section), 1356843144);
	}

	/**
	 * Adds a route to the router into the specified section
	 * 
	 * @param Route $route The route to add
	 * @param string $section The route section for the added route
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
