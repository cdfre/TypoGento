<?php

namespace Tx\Typogento\Utility;

/**
 * Frontend plugin helper
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class PluginUtility {
	
	const FLEXFORM_FIELD_DISPLAY_TYPE = 'settings.display.type';
	
	const FLEXFORM_FIELD_DISPLAY_PRODUCT = 'settings.display.product';
	
	const FLEXFORM_FIELD_DISPLAY_CATEGORY = 'settings.display.category';
	
	const FLEXFORM_FIELD_DISPLAY_ROUTE = 'settings.display.route';
	
	const FLEXFORM_FIELD_DISPLAY_CONTROLLER = 'settings.display.controller';
	
	const FLEXFORM_FIELD_DISPLAY_ACTION = 'settings.display.action';
	
	const FLEXFORM_FIELD_CACHE_DISABLE = 'settings.cache.disable';
	
	const FLEXFORM_SHEET_DISPLAY = 'display';
	
	const FLEXFORM_SHEET_CACHE = 'cache';
	
	/**
	 * Get plugin configuration from FlexForm
	 * 
	 * @param array $flexform The FlexForm array
	 * @return array 
	 * @throws Exception 
	 */
	public static function getFlexFormConfiguration(array &$flexform) {
		$type = GeneralUtility::getFlexFormValue($flexform, self::FLEXFORM_FIELD_DISPLAY_TYPE, self::FLEXFORM_SHEET_DISPLAY);
		// result
		$result = null;
		// transform
		switch ($type) {
			case "PRODUCT":
				$result = array(
					'route' => 'catalog', 'controller' => 'product', 'action' => 'view', 
					'id' => GeneralUtility::getFlexFormValue($flexform, self::FLEXFORM_FIELD_DISPLAY_PRODUCT, self::FLEXFORM_SHEET_DISPLAY)
				);
				break;
			case "CATEGORY":
				$result = array(
					'route' => 'catalog', 'controller' => 'category', 'action' => 'view', 
					'id' => GeneralUtility::getFlexFormValue($flexform, self::FLEXFORM_FIELD_DISPLAY_CATEGORY, self::FLEXFORM_SHEET_DISPLAY)
				);
				break;
			case "USER":
				$result = array(
					'route' => GeneralUtility::getFlexFormValue($flexform, self::FLEXFORM_FIELD_DISPLAY_ROUTE, self::FLEXFORM_SHEET_DISPLAY),
					'controller' => GeneralUtility::getFlexFormValue($flexform, self::FLEXFORM_FIELD_DISPLAY_CONTROLLER, self::FLEXFORM_SHEET_DISPLAY),
					'action' => GeneralUtility::getFlexFormValue($flexform, self::FLEXFORM_FIELD_DISPLAY_ACTION, self::FLEXFORM_SHEET_DISPLAY)
				);
				break;
			default:
				throw new Exception(sprintf('Unexpected view type "%s".', $type), 1357002849);
		}
		// caching
		$result['cache'] = !(bool)GeneralUtility::getFlexFormValue($flexform, self::FLEXFORM_FIELD_CACHE_DISABLE, self::FLEXFORM_SHEET_CACHE);
		// return result
		return $result;
	}
}
?>