<?php

/**
 * Frontend plugin helper
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_pi1_helper {
	
	/**
	 * Get plugin configuration from FlexForm
	 * 
	 * @param array $flexform The FlexForm array
	 * @return array 
	 * @throws Exception 
	 */
	public function getFlexFormConfiguration(array &$flexform) {
		$sheet = 'display';
		$type = tx_typogento_div::getFlexFormValue($flexform, 'type', $sheet);
		// result
		$result = null;
		// transform
		switch ($type) {
			case "PRODUCT":
				$result = array(
					'route' => 'catalog', 'controller' => 'product', 'action' => 'view', 
					'id' => tx_typogento_div::getFlexFormValue($flexform, 'product', $sheet)
				);
				break;
			case "CATEGORY":
				$result = array(
					'route' => 'catalog', 'controller'=>'category', 'action' => 'view', 
					'id' => tx_typogento_div::getFlexFormValue($flexform, 'category', $sheet)
				);
				break;
			case "USER":
				$result = array(
					'route' => tx_typogento_div::getFlexFormValue($flexform, 'route', $sheet),
					'controller' => tx_typogento_div::getFlexFormValue	($flexform, 'controller', $sheet),
					'action' => tx_typogento_div::getFlexFormValue($flexform, 'action', $sheet)
				);
				break;
			default:
				throw tx_typogento_div::exception('lib_view_type_not_valid_error',
					array($type)
				);
		}
		// caching
		$result['cache'] = !(bool)tx_typogento_div::getFlexFormValue($flexform, 'disable', 'cache');
		// return result
		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/pi1/class.tx_typogento_pi1_helper.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/pi1/class.tx_typogento_pi1_helper.php']);
}