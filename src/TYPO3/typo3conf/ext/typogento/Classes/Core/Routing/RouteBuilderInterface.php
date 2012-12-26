<?php 

namespace Tx\Typogento\Core\Routing;

/**
 * TypoGento route builder interface
 *
 * Interface for a route builder creating routes for the TypoGento router.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
interface RouteBuilderInterface {

	/**
	 * Build the routes
	 *
	 * @param Router $router The router to setup
	 */
	function build(Router $router);
}
?>