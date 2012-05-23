<?php 

/**
 * TypoGento route filter
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
interface tx_typogento_routeFilter {
	
	function match();
}

/**
 * TypoGento TypoScript route filter
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_typoscriptRouteFilter implements tx_typogento_routeFilter {
	
	protected $_conf = null;
	
	public function __construct(array $conf) {
		$this->_conf = $conf;
	}
	
	public function match() {
		$cObj = tx_typogento_div::getContentObject();
		return $cObj->checkIf($this->_conf);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/routing/class.tx_typogento_routefilter.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/routing/class.tx_typogento_routefilter.php']);
}

?>