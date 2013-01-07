<?php

namespace Tx\Typogento\Hook;

use \Tx\Typogento\Utility\GeneralUtility;
use \Tx\Typogento\Utility\PluginUtility;

/**
 * Backend service
 *
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class ItemsProcFuncHook {

	public function getReplicationProviders(&$params, &$pObj) {
		$this->getSoapItems(
				function ($soap) use (&$params) {
					return $soap->typogento_replication()->providers();
				},
				function (&$value, $key) use (&$params) {
					$params['items'][] = array($value['display'], $value['id']);
				}
		);
	}
	
	public function getReplicationSources(&$params, &$pObj) {
		$this->getSoapItems(
			function ($soap) use (&$params) {
				return $soap->typogento_replication()->sources($params['row']['provider']);
			},
			function (&$value, $key) use (&$params) {
				$params['items'][] = array($value['display'], $value['id']);
			}
		);
	}
	
	public function getReplicationTargets(&$params, &$pObj) {
		$this->getSoapItems(
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
	public function getCatalogProducts(&$params, &$pObj) {
		$this->getSoapItems(
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
	public function getCustomers(&$params, &$pObj) {
		$this->getSoapItems(
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
	public function getAdministrationRoles(&$params, &$pObj) {
		$this->getSoapItems(
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
	public function getCustomerGroups(&$params, &$pObj) {
		$this->getSoapItems(
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
	public function getSystemModules(&$params, &$pObj) {
		$this->getSoapItems(
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
	public function getSystemControllers(&$params, &$pObj) {

		$module = $this->getFlexformData($pObj, PluginUtility::FLEXFORM_FIELD_DISPLAY_ROUTE, PluginUtility::FLEXFORM_SHEET_DISPLAY);
		if (!$module) {
			return;
		}
		
		$this->getSoapItems(
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
	public function getSystemActions(&$params, &$pObj) {

		$module = $this->getFlexformData($pObj, PluginUtility::FLEXFORM_FIELD_DISPLAY_ROUTE, PluginUtility::FLEXFORM_SHEET_DISPLAY);
		if (!$module) {
			return;
		}

		$controller = $this->getFlexformData($pObj, PluginUtility::FLEXFORM_FIELD_DISPLAY_CONTROLLER, PluginUtility::FLEXFORM_SHEET_DISPLAY);
		if (!$controller) {
			return;
		}

		$this->getSoapItems(
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
	public function getStoreViews(&$params, &$pObj) {

		$this->getSoapItems(
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
	public function getCatalogCategories(&$params, &$pObj) {
		$walk = function (&$value, $key = null) use(&$params, &$walk) {
			$params['items'][] = array(str_repeat('-', $value['level']*1) . $value['name'], $value['category_id']);
			
			if (is_array($value['children'])) {
				foreach ($value['children'] as $child) {
					$walk($child);
				}
			}
		};
		$this->getSoapItems(
			function ($soap) {
				return array($soap->catalog_category()->tree());
			},
			$walk
		);
	}
	
	protected function getSoapItems($fetch, $walk, $data = null) {
		try {
			// try to fetch the data with soap
			$soap = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Service\\SoapService');
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
		} catch (\Exception $e) {
			// get error message
			$message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('\\TYPO3\\CMS\\Core\\Messaging\\FlashMessage', 
				$e->getMessage(), '', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
			);
			\TYPO3\CMS\Core\Messaging\FlashMessageQueue::addMessage($message);
		}
	}

	/**
	 * Returns the Value of an Flexform Field from TCEforms
	 *
	 * @param \TYPO3\CMS\Backend\Form\FormEngine $TCEforms
	 * @param string $field
	 * @param string $sheet
	 * @param string $lang
	 * @param string $value
	 * @return unknown
	 */
	protected function getFlexformData(\TYPO3\CMS\Backend\Form\FormEngine $engine, $field, $sheet='sDEF', $lang='lDEF', $value='vDEF') {
		try {
			$data = current($engine->cachedTSconfig);
			$flexform = $data['_THIS_ROW']['pi_flexform'];
			$flexformArray = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($flexform);

			return GeneralUtility::getFlexFormValue($flexformArray, $field, $sheet, $lang, $value);
		} catch (\Exception $e) {
			return null;
		}
	}
}
?>
