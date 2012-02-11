<?php

/**
 * TypoGento TCA fields
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_weetypogento_tcafields {

	/**
	 * Generates an Productlist as Array for TCA Select fields
	 *
	 * @param array $params
	 * @param object $pObj
	 */
	public function itemsProcFunc_products(&$params,&$pObj) {
		$this->_getSoapItems(
			function ($soap) {
				return $soap->catalog_product()->list();
			},
			function (&$value, $key) use (&$params) {
				$params['items'][] = array($value['name'] . ' - ' . $value['sku'], $value['product_id']);
			}
		);
	}

	/**
	 * Generates a Grouplist as Array for TCA Select fields
	 *
	 * @param array $params
	 * @param object $pObj
	 */
	public function itemsProcFunc_usergroups(&$params, &$pObj) {
		$this->_getSoapItems(
			function ($soap) {
				return $soap->typogento_admin_roles()->list();
			},
			function (&$value, $key) use (&$params) {
				$params['items'][] = array($value['label'], $value['value']);
			}
		);
	}

	/**
	 * Generates a frontend Grouplist as Array for TCA Select fields
	 *
	 * @param array $params
	 * @param object $pObj
	 */
	public function itemsProcFunc_feusergroups(&$params, &$pObj) {
		$this->_getSoapItems(
			function ($soap) {
				return $soap->customer_group()->list();
			},
			function (&$value, $key) use (&$params) {
				$params['items'][] = array($value['customer_group_code'], $value['customer_group_id']);
			}
		);
	}

	/**
	 * Generates an Modulelist as Array for TCA Select fields
	 *
	 * @param array $params
	 * @param object $pObj
	 */
	public function itemsProcFunc_modules(&$params, &$pObj) {
		$this->_getSoapItems(
			function ($soap) {
				return $soap->typogento_modules()->list();
			},
			function (&$value, $key) use (&$params) {
				$params['items'][] = array(ucfirst($value), $value);
			}
		);
	}

	/**
	 * Generates an Controllerlist as Array for TCA Select fields
	 *
	 * @param array $params
	 * @param object $pObj
	 */
	public function itemsProcFunc_controllers(&$params, &$pObj) {

		$module = $this->_getFlexformData($pObj, 'route', 'main');
		if (!$module) {
			return;
		}
		
		$this->_getSoapItems(
			function ($soap) use (&$module) {
				return $soap->typogento_modules()->controllers($module);
			},
			function (&$value, $key) use (&$params) {
				$params['items'][] = array($value, $value);
			}
		);
	}

	/**
	 * Generates an Actionlist as Array for TCA Select fields
	 *
	 * @param array $params
	 * @param object $pObj
	 */
	public function itemsProcFunc_actions(&$params, &$pObj) {

		$module = $this->_getFlexformData($pObj, 'route', 'main');
		if (!$module) {
			return;
		}

		$controller = $this->_getFlexformData($pObj, 'controller', 'main');
		if (!$controller) {
			return;
		}

		$this->_getSoapItems(
			function ($soap) use (&$module, &$controller) {
				return $soap->typogento_modules()->actions($module, $controller);
			},
			function (&$value, $key) use (&$params) {
				$params['items'][] = array($value, $value);
			}
		);
	}

	/**
	 * Generates an Storeviewlist as Array for TCA Select fields
	 *
	 * @param array $params
	 * @param object $pObj
	 */
	public function itemsProcFunc_languages(&$params, &$pObj) {

		$this->_getSoapItems(
			function ($soap) {
				return $soap->typogento_stores()->list();
			},
			function (&$value, $key) use (&$params) {
				$params['items'][] = array($value['label'], $value['value']);
			}
		);
	}

	/**
	 * Generates an Category as Array for TCA Select fields
	 *
	 * @param array $params
	 * @param object $pObj
	 */
	public function itemsProcFunc_categories(&$params, &$pObj) {
		$walk = function (&$value, $key = null) use(&$params, &$walk) {
			$params['items'][] = array(str_repeat('-', $value['level']*1) . $value['name'], $value['category_id']);
			
			if (!is_array($value['children'])) {
				return;
			}
			
			foreach ($value['children'] as $child) {
				return $walk($child);
			}
		};
		$this->_getSoapItems(
			function ($soap) {
				return array($soap->catalog_category()->tree());
			},
			$walk
		);
	}
	
	protected function _getSoapItems($fetch, $walk) {
		$conf = tx_weetypogento_tools::getExtConfig();
		
		try {
			$soap = t3lib_div::makeInstance('tx_weetypogento_soapinterface');
			$result = $fetch($soap);
			
			if (!isset($result)) {
				return;
			}
			
			if (is_array($result)) {
				return array_walk($result, $walk);
			} else {
				return $walk($result);
			}
		} catch (Exception $e) {
			$message = t3lib_div::makeInstance('t3lib_FlashMessage', $e->getMessage(), 'SOAP Request Failed', t3lib_FlashMessage::ERROR);
			t3lib_FlashMessageQueue::addMessage($message);
		}
		
		
	}

	/**
	 * Returns the Value of an Flexform Field from TCEforms
	 *
	 * @param t3lib_TCEforms $TCEforms
	 * @param string $fieldName
	 * @param string $sheet
	 * @param string $lang
	 * @param string $value
	 * @return unknown
	 */
	protected function _getFlexformData(t3lib_TCEforms &$TCEforms, $fieldName, $sheet='sDEF', $lang='lDEF', $value='vDEF') {
		try {
			$data = current($TCEforms->cachedTSconfig);
			$flexform = $data['_THIS_ROW']['pi_flexform'];
			$flexformArray = t3lib_div::xml2array($flexform);

			return tx_weetypogento_tools::getFFvalue($flexformArray, $fieldName, $sheet, $lang, $value);
		} catch (Exception $e) {
			return null;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_tcafields.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_tcafields.php']);
}

?>
