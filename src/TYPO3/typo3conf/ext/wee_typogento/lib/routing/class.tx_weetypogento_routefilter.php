<?php 

/**
 * TypoGento route filter
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
interface tx_weetypogento_routeFilter {
	
	function match();
}

/**
 * TypoGento TypoScript route filter
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_weetypogento_typoscriptRouteFilter implements tx_weetypogento_routeFilter {
	
	protected $_conf = null;
	
	public function __construct(array $conf) {
		$this->_conf = $conf;
	}
	
	public function match() {
		$cObj = tx_weetypogento_tools::getContentObject();
		return $cObj->checkIf($this->_conf);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/routing/class.tx_weetypogento_routefilter.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/routing/class.tx_weetypogento_routefilter.php']);
}

?>