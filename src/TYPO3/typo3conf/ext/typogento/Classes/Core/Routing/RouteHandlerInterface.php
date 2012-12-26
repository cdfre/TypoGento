<?php 

namespace Tx\Typogento\Core\Routing;

/**
 * Route handler interface
 *
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
interface RouteHandlerInterface {

	/**
	 * Processes the route
	 * 
	 * @return mixed The processing result.
	 */
	function process();
}
?>