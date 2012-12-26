<?php 

namespace Tx\Typogento\Core\Routing;

/**
 * Route filter interface
 *
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
interface RouteFilterInterface {
	
	/**
	 * Checks if the filter matches
	 * 
	 * @return boolean Returns true if the filter matches otherwise false.
	 */
	function match();
}
?>