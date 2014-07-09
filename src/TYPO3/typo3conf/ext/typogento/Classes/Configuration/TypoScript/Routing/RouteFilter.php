<?php 

namespace Tx\Typogento\Configuration\TypoScript\Routing;

use Tx\Typogento\Utility\GeneralUtility;

/**
 * Default TypoScript route filter
 * 
 * Default route filter implementation using the TypoScript function 'if'.
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class RouteFilter implements \Tx\Typogento\Core\Routing\RouteFilterInterface {
	
	protected $typoscript = null;
	
	public function __construct(array $typoscript) {
		$this->typoscript = &$typoscript;
	}
	
	/**
	 * Uses the TypoScript function 'if'
	 * 
	 * @see \Tx\Typogento\Core\Routing\RouteFilterInterface::match()
	 */
	public function match() {
		$content = GeneralUtility::getContentObject();
		return $content->checkIf($this->typoscript);
	}
}
?>