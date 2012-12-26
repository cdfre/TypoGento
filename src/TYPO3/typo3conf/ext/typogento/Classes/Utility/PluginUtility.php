<?php

namespace Tx\Typogento\Utility;

/**
 * Frontend plugin helper
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class PluginUtility {
	
	/**
	 * Get plugin configuration from FlexForm
	 * 
	 * @param array $flexform The FlexForm array
	 * @return array 
	 * @throws Exception 
	 */
	public static function getFlexFormConfiguration(array &$flexform) {
		$sheet = 'display';
		$type = GeneralUtility::getFlexFormValue($flexform, 'type', $sheet);
		// result
		$result = null;
		// transform
		switch ($type) {
			case "PRODUCT":
				$result = array(
					'route' => 'catalog', 'controller' => 'product', 'action' => 'view', 
					'id' => GeneralUtility::getFlexFormValue($flexform, 'product', $sheet)
				);
				break;
			case "CATEGORY":
				$result = array(
					'route' => 'catalog', 'controller'=>'category', 'action' => 'view', 
					'id' => GeneralUtility::getFlexFormValue($flexform, 'category', $sheet)
				);
				break;
			case "USER":
				$result = array(
					'route' => GeneralUtility::getFlexFormValue($flexform, 'route', $sheet),
					'controller' => GeneralUtility::getFlexFormValue($flexform, 'controller', $sheet),
					'action' => GeneralUtility::getFlexFormValue($flexform, 'action', $sheet)
				);
				break;
			default:
				throw new Exception(sprintf('Unexpected view type "%s".', $type), 1357002849);
		}
		// caching
		$result['cache'] = !(bool)GeneralUtility::getFlexFormValue($flexform, 'disable', 'cache');
		// return result
		return $result;
	}
}
?>