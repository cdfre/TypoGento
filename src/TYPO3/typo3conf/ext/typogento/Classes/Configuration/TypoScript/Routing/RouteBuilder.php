<?php 

namespace Tx\Typogento\Configuration\TypoScript\Routing;

use \Tx\Typogento\Utility\GeneralUtility;
use \Tx\Typogento\Core\Routing\RouteBuilderInterface;
use \Tx\Typogento\Core\Routing\Router;
use \Tx\Typogento\Core\Routing\Route;

/**
 * Default TypoScript route builder
 *
 * Setup the TypoGento routes using TypoScript.
 *
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class RouteBuilder implements RouteBuilderInterface {

	/**
	 * Build the routes
	 *
	 * Uses the TypoScript setup and caches the FlexForm route path.
	 *
	 * @see RouteBuilderInterface::build()
	 */
	public function build(Router $router) {
		// get configuration helper
		$helper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Configuration\\ConfigurationManager');
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
			$route = $this->buildRoute($conf);
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
	protected function buildRoute(array &$typoscript) {
		if (!isset($typoscript['filter.'])) {
			throw new Exception(sprintf('Missing the route filter in "%s"', print_r($typoscript, true)), 1356838587);
		}

		if (!isset($typoscript['target.'])) {
			throw new Exception(sprintf('Missing the route target in "%s"', print_r($typoscript, true)), 1356838639);
		}

		$priority = $this->buildValue('priority', $typoscript, 0);

		$filter = new RouteFilter($typoscript['filter.']);
		$handler = new RouteHandler($typoscript['target.']);

		return new Route($filter, $handler, $priority);
	}

	/**
	 * Helper function
	 *
	 * @param unknown_type $key
	 * @param array $conf
	 * @param unknown_type $default
	 */
	protected function buildValue($key, array &$typoscript, $default) {
		$cObj = GeneralUtility::getContentObject();
		return isset($typoscript[$key.'.']) 
			? trim($cObj->stdWrap($typoscript['$key'], $typoscript[$key.'.'])) 
			: isset($typoscript[$key])
				? trim($typoscript[$key]) 
				: $default;
	}
}
?>