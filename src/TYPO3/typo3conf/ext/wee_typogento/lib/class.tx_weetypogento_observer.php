<?php

/**
 * TypoGento observer
 * 
 * Observes TYPO3 system hooks.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_weetypogento_observer implements t3lib_Singleton, tslib_content_getDataHook {
	
	/**
	 * Initialize Magento
	 *
	 * @deprecated No longer necessary with http://forge.typo3.org/projects/typo3v4-core/repository/revisions/3e18ab8726e5586d9ef8888ffce49a6cf7e03b53
	 * @todo Make sure it's realy deprecated
	 */
	public function preprocessRequest($params, &$pObj) {
		// init magento
		//t3lib_div::makeInstance('tx_weetypogento_autoloader');
	}
	/**
	 * Integrate Magento head block
	 * 
	 * If 'auto_header' is enabled the Magento header gets 
	 * loaded into the current TYPO3 page header.
	 *
	 * @param array $params
	 * @param t3lib_pagerenderer $pObj
	 */
	public function renderPreProcess($params, t3lib_pagerenderer &$pObj) {
		// get configuration helper
		$helper = t3lib_div::makeInstance('tx_weetypogento_configurationHelper');
		// get plugin setup
		$setup = $helper->getSection(tx_weetypogento_configurationHelper::TYPOSCRIPT_SETUP);
		// integrate magento resources into typo3 header
		try {
			// integrate magento header using the default block
			$header = t3lib_div::makeInstance('tx_weetypogento_header');
			// render magento resources into typo3 header
			$header->render();
		} catch (Exception $e) {
			tx_weetypogento_tools::throwException('lib_page_head_integration_failed_error',
				array($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']), $e
			);
		}
	}
	
	/**
	 * Cache the Magento route path of the TYPO3 page content
	 * 
	 * @param array $params
	 * @param tslib_fe $pObj
	 */
	public function configArrayPostProc($params, tslib_fe &$pObj) {
		// check if route set from magento frontend plugin is not set
		//if (!isset($pObj->config['tx_weetypogento'])) {
		try {
			t3lib_div::makeInstance('tx_weetypogento_router');
		} catch(Exception $e) {
			tx_weetypogento_tools::throwException('lib_routing_system_initalizing_failed_error', 
				array($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']), $e
			);
		}
		//}
	}
	
	public function getDataExtension($getDataString, array $fields, $sectionValue, $returnValue, tslib_cObj &$parentObject) {
		// check if nothing to do
		if (!empty($returnValue)
		|| strpos($sectionValue, 'register') !== 0
		|| strpos($sectionValue, 'tx_weetypogento.') === false) {
			return $returnValue;
		}
		// check if register:tx_weetypogento.page.<field> is used
		if (strpos($sectionValue, 'tx_weetypogento.page.') !== false) {
			$parts = explode(':', $sectionValue, 2);
			$field = strtolower(substr(trim($parts[1]), 21));
			// get the magento head block
			$header = t3lib_div::makeInstance('tx_weetypogento_header');
			$block = $header->getBlock();
			// check if <field> exist
			if (!$block->hasData($field)) {
				return $returnValue;
			}
			// get <field> value
			$data = $block->getData($field);
			// return result
			return strval($data);
		}
		
		return $returnValue;
	}
	
	/**
	 * Logoff Hook
	 *
	 * @param array $params
	 * @param t3lib_userAuth $pObj
	 */
	public function logoffPreProcessing($params, &$pObj) {
		// skip if not logout
		if (t3lib_div::_GP('logintype') != 'logout'
		|| $pObj->loginType != 'FE') {
			return;
		}
		// init magento
		t3lib_div::makeInstance('tx_weetypogento_autoloader');
		// 
		Mage::app();
		// load the frontend session
		Mage::getSingleton('core/session', array('name' => 'frontend'));
		// get session
		$session = Mage::getModel('customer/session');
		// logout if session is logged in
		$session->logout();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_observer.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_observer.php']);
}

?>
