<?php

/**
 * TypoGento TCA fields
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_tcafields {
	
	public function itemsProcFunc_replicationLinkLabel(&$params, &$pObj) {
		$params['title'] = $GLOBALS['LANG']->sL("LLL:EXT:typogento/locallang_db.xml:tx_typogento_replication_links.provider.{$params['row']['provider']}");
	}

	public function itemsProcFunc_replicationSources(&$params, &$pObj) {
		$this->_getSoapItems(
			function ($soap) use (&$params) {
				return $soap->typogento_replication()->sources($params['row']['provider']);
			},
			function (&$value, $key) use (&$params) {
				$params['items'][] = array($value['display'], $value['id']);
			}
		);
	}
	
	public function itemsProcFunc_replicationTargets(&$params, &$pObj) {
		$this->_getSoapItems(
			function ($soap) use (&$params) {
				return $soap->typogento_replication()->targets($params['row']['provider']);
			},
			function (&$value, $key) use (&$params) {
				$params['items'][] = array($value['display'], $value['id']);
			}
		);
	}
	
	/**
	 * Generates an Productlist as Array for TCA Select fields
	 *
	 * @param array $params
	 * @param object $pObj
	 */
	public function itemsProcFunc_products(&$params, &$pObj) {
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
	 * Generates a Customerlist as Array for TCA Select fields
	 *
	 * @param array $params
	 * @param object $pObj
	 */
	public function itemsProcFunc_customers(&$params, &$pObj) {
		$this->_getSoapItems(
			function ($soap) {
				return $soap->customer()->list();
			},
			function (&$value, $key, &$data) use (&$params) {
				if (array_search($value['customer_id'], $data) === false) {
					$params['items'][] = array("{$value['lastname']}, {$value['firstname']} ({$value['email']})", $value['customer_id']);
				}
			},
			function &() use (&$params) {
				$result = array();
				$resource = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_typogento_customer',
					'fe_users', "deleted = 0 AND tx_typogento_customer <> 0 AND pid = {$params['row']['pid']} AND uid <> {$params['row']['uid']}"
				);
				while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resource)) !== false) {
					$result[] = $row['tx_typogento_customer'];
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($resource);
				return $result;
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

		$module = $this->_getFlexformData($pObj, 'route', 'display');
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

		$module = $this->_getFlexformData($pObj, 'route', 'display');
		if (!$module) {
			return;
		}

		$controller = $this->_getFlexformData($pObj, 'controller', 'display');
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
			
			if (is_array($value['children'])) {
				foreach ($value['children'] as $child) {
					$walk($child);
				}
			}
		};
		$this->_getSoapItems(
			function ($soap) {
				return array($soap->catalog_category()->tree());
			},
			$walk
		);
	}
	
	protected function _getSoapItems($fetch, $walk, $data = null) {
		try {
			// try to fetch the data with soap
			$soap = t3lib_div::makeInstance('tx_typogento_soapinterface');
			$result = $fetch($soap);
			// skip if nothing returned
			if (!isset($result)) {
				return;
			}
			// transform the items
			if (is_array($result)) {
				if (isset($data)) {
					return array_walk($result, $walk, $data());
				} else {
					return array_walk($result, $walk);
				}
			} else {
				return $walk($result);
			}
		} catch (Exception $e) {
			// get error message
			$message = t3lib_div::makeInstance('t3lib_FlashMessage', 
				$e->getMessage(), '', t3lib_FlashMessage::ERROR
			);
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

			return tx_typogento_div::getFlexFormValue($flexformArray, $fieldName, $sheet, $lang, $value);
		} catch (Exception $e) {
			return null;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_tcafields.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_tcafields.php']);
}

?>
